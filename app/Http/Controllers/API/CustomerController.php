<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     * GET /api/customers
     */
    public function index(Request $request)
    {
        $query = User::where('role_id', 4);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active') && $request->is_active !== null) {
            $query->where('is_active', $request->is_active);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Get customers with order statistics
        $query->withCount('orders')
              ->with('center:id,name')
              ->selectRaw('users.*,
                          (SELECT SUM(total_amount) FROM orders WHERE orders.user_id = users.id AND orders.status = "delivered") as total_spent,
                          (SELECT MAX(created_at) FROM orders WHERE orders.user_id = users.id) as last_order_date');

        $perPage = $request->get('per_page', 15);
        $customers = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Customers retrieved successfully',
            'data' => $customers
        ]);
    }

    /**
     * Store a newly created customer
     * POST /api/customers
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => 4, // Customer role
            ]);

            // Load relationships
            $customer->load('center:id,name');
            $customer->loadCount('orders');

            return response()->json([
                'status' => 'success',
                'message' => 'Customer created successfully',
                'data' => $customer
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified customer
     * GET /api/customers/{id}
     */
    public function show($id)
    {
        $customer = User::where('role_id', 4)
            ->where('id', $id)
            ->withCount('orders')
            ->with('center:id,name')
            ->selectRaw('users.*,
                        (SELECT SUM(total_amount) FROM orders WHERE orders.user_id = users.id AND orders.status = "delivered") as total_spent,
                        (SELECT MAX(created_at) FROM orders WHERE orders.user_id = users.id) as last_order_date')
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Customer retrieved successfully',
            'data' => $customer
        ]);
    }

    /**
     * Get customer profile with detailed statistics
     * GET /api/customers/{id}/profile
     */
    public function profile($id)
    {
        $customer = User::where('role_id', 4)
            ->where('id', $id)
            ->with('center:id,name')
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        // Get recent orders
        $recentOrders = Order::where('user_id', $id)
            ->with(['items.commodity:id,name'])
            ->select('id', 'order_number', 'total_amount', 'status', 'created_at')
            ->selectRaw('(SELECT COUNT(*) FROM order_items WHERE order_items.order_id = orders.id) as items_count')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate statistics
        $statistics = [
            'total_orders' => Order::where('user_id', $id)->count(),
            'completed_orders' => Order::where('user_id', $id)
                ->where('status', Order::STATUS_DELIVERED)
                ->count(),
            'pending_orders' => Order::where('user_id', $id)
                ->whereIn('status', [
                    Order::STATUS_PENDING,
                    Order::STATUS_DEPOSIT_REQUESTED,
                    Order::STATUS_DEPOSIT_PAID,
                    Order::STATUS_IN_PRODUCTION
                ])
                ->count(),
            'cancelled_orders' => Order::where('user_id', $id)
                ->where('status', Order::STATUS_CANCELLED)
                ->count(),
            'total_spent' => (float) Order::where('user_id', $id)
                ->where('status', Order::STATUS_DELIVERED)
                ->sum('total_amount'),
            'average_order_value' => (float) Order::where('user_id', $id)
                ->where('status', Order::STATUS_DELIVERED)
                ->avg('total_amount'),
            'last_order_date' => Order::where('user_id', $id)
                ->max('created_at')
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Customer profile retrieved successfully',
            'data' => [
                'customer' => $customer,
                'recent_orders' => $recentOrders,
                'statistics' => $statistics
            ]
        ]);
    }

    /**
     * Get customer orders
     * GET /api/customers/{id}/orders
     */
    public function orders($id, Request $request)
    {
        $customer = User::where('role_id', 4)->find($id);

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        $query = Order::where('user_id', $id)
            ->with([
                'items.commodity:id,name',
                'supplier:id,name',
                'payments'
            ]);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer orders retrieved successfully',
            'data' => [
                'customer' => $customer,
                'orders' => $orders
            ]
        ]);
    }

    /**
     * Update the specified customer
     * PUT /api/customers/{id}
     */
    public function update(Request $request, $id)
    {
        $customer = User::where('role_id', 4)->find($id);

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer->update($request->only([
                'name',
                'email',
                'phone_number',
                'address',
                'city',
                'country',
                'is_active'
            ]));

            // Reload relationships
            $customer->load('center:id,name');
            $customer->loadCount('orders');

            return response()->json([
                'status' => 'success',
                'message' => 'Customer updated successfully',
                'data' => $customer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle customer active status
     * PATCH /api/customers/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        $customer = User::where('role_id', 4)->find($id);

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        try {
            $customer->is_active = !$customer->is_active;
            $customer->save();

            // Reload relationships
            $customer->load('center:id,name');
            $customer->loadCount('orders');

            return response()->json([
                'status' => 'success',
                'message' => 'Customer status toggled successfully',
                'data' => $customer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle customer status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified customer
     * DELETE /api/customers/{id}
     */
    public function destroy($id)
    {
        $customer = User::where('role_id', 4)->find($id);

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        try {
            // Check if customer has orders
            $hasOrders = Order::where('user_id', $id)->exists();

            if ($hasOrders) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete customer with existing orders. Please deactivate instead.'
                ], 422);
            }

            $customer->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Customer deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer statistics
     * GET /api/customers/statistics
     */
    public function statistics()
    {
        try {
            $totalCustomers = User::where('role_id', 4)->count();
            $activeCustomers = User::where('role_id', 4)->where('is_active', true)->count();
            $inactiveCustomers = User::where('role_id', 4)->where('is_active', false)->count();

            // New customers this month
            $newCustomersThisMonth = User::where('role_id', 4)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();

            // Customers with orders
            $customersWithOrders = User::where('role_id', 4)
                ->whereHas('orders')
                ->count();

            // Top customers by spending
            $topCustomers = User::where('role_id', 4)
                ->withCount('orders')
                ->selectRaw('users.*,
                            (SELECT SUM(total_amount) FROM orders WHERE orders.user_id = users.id AND orders.status = "delivered") as total_spent')
                ->having('total_spent', '>', 0)
                ->orderBy('total_spent', 'desc')
                ->limit(10)
                ->get();

            // Total customer spending
            $totalCustomerSpending = (float) Order::where('status', Order::STATUS_DELIVERED)
                ->sum('total_amount');

            // Average customer value
            $averageCustomerValue = $customersWithOrders > 0
                ? $totalCustomerSpending / $customersWithOrders
                : 0;

            $statistics = [
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
                'inactive_customers' => $inactiveCustomers,
                'new_customers_this_month' => $newCustomersThisMonth,
                'customers_with_orders' => $customersWithOrders,
                'top_customers' => $topCustomers,
                'total_customer_spending' => $totalCustomerSpending,
                'average_customer_value' => $averageCustomerValue
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Customer statistics retrieved successfully',
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch customer statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send welcome email to customer
     * POST /api/customers/{id}/send-welcome-email
     */
    public function sendWelcomeEmail($id)
    {
        $customer = User::where('role_id', 4)->find($id);

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        try {
            // Send welcome email
            Mail::send('emails.customer-welcome', ['customer' => $customer], function($message) use ($customer) {
                $message->to($customer->email, $customer->name)
                        ->subject('Welcome to Our Platform');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Welcome email sent successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send welcome email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset customer password
     * POST /api/customers/{id}/reset-password
     */
    public function resetPassword($id)
    {
        $customer = User::where('role_id', 4)->find($id);

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        try {
            // Generate password reset token
            $token = Password::createToken($customer);

            // Send password reset email
            Mail::send('emails.password-reset', [
                'customer' => $customer,
                'token' => $token,
                'resetUrl' => url("/reset-password?token={$token}&email={$customer->email}")
            ], function($message) use ($customer) {
                $message->to($customer->email, $customer->name)
                        ->subject('Password Reset Request');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset email sent successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send password reset email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}