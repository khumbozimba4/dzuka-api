<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Center;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ArtisanController extends Controller
{
    /**
     * Display paginated listing of artisans/suppliers
     * GET /api/artisans
     */
    public function index(Request $request)
    {
        $query = Supplier::with(['category', 'center'])
            ->orderBy('name');

        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by center if provided
        if ($request->has('center_id')) {
            $query->where('center_id', $request->center_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        } else {
            // Default to active artisans only
            $query->active();
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%")
                  ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        $artisans = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $artisans,
            'message' => 'Artisans retrieved successfully'
        ]);
    }

    /**
     * Get artisans grouped by categories
     * GET /api/artisans/by-category
     */
    public function getByCategory()
    {
        $categories = Category::with(['suppliers' => function($query) {
            $query->active()
                  ->with('center')
                  ->orderBy('name');
        }])
        ->whereHas('suppliers', function($query) {
            $query->active();
        })
        ->orderBy('category_name')
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories,
            'message' => 'Artisans by category retrieved successfully'
        ]);
    }

    /**
     * Get artisans by specific category
     * GET /api/artisans/category/{categoryId}
     */
    public function getByCategoryId($categoryId)
    {
        $category = Category::findOrFail($categoryId);

        $artisans = Supplier::with(['center'])
            ->active()
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'category' => $category,
                'artisans' => $artisans
            ],
            'message' => "Artisans for {$category->name} category retrieved successfully"
        ]);
    }

    /**
     * Get artisans by center
     * GET /api/artisans/center/{centerId}
     */
    public function getByCenterId($centerId)
    {
        $center = Center::findOrFail($centerId);

        $artisans = Supplier::with(['category'])
            ->active()
            ->where('center_id', $centerId)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'center' => $center,
                'artisans' => $artisans
            ],
            'message' => "Artisans for {$center->name} center retrieved successfully"
        ]);
    }

    /**
     * Store a newly created artisan (Admin only)
     * POST /api/artisans
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:suppliers',
            'location' => 'required|string|max:255',
            'pin' => 'required|string|min:4|max:6',
            'category_id' => 'required|exists:categories,id',
            'center_id' => 'required|exists:centers,id',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $artisanData = $request->only([
            'name', 'phone_number', 'location', 'category_id', 'center_id'
        ]);

        // Hash the PIN
        $artisanData['pin'] = Hash::make($request->pin);
        $artisanData['is_active'] = $request->get('is_active', true);

        $artisan = Supplier::create($artisanData);
        $artisan->load(['category', 'center']);

        return response()->json([
            'status' => 'success',
            'data' => $artisan,
            'message' => 'Artisan created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified artisan with full details
     * GET /api/artisans/{id}
     */
    public function show($id)
    {
        $artisan = Supplier::with(['category', 'center'])
            ->withCount(['orders', 'completedOrders'])
            ->findOrFail($id);

        // Add calculated fields
        $artisan->total_commission = $artisan->total_commission;

        return response()->json([
            'status' => 'success',
            'data' => $artisan,
            'message' => 'Artisan details retrieved successfully'
        ]);
    }

    /**
     * Get artisan profile with orders and statistics
     * GET /api/artisans/{id}/profile
     */
    public function getProfile($id)
    {
        $artisan = Supplier::with(['category', 'center'])
            ->withCount(['orders', 'completedOrders'])
            ->findOrFail($id);

        // Get recent orders
        $recentOrders = Order::with(['customer', 'items.commodity'])
            ->where('supplier_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate statistics
        $stats = [
            'total_orders' => $artisan->orders_count,
            'completed_orders' => $artisan->completed_orders_count,
            'pending_orders' => $artisan->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'total_commission' => $artisan->total_commission,
            'average_order_value' => $artisan->orders()->avg('total_amount'),
            'completion_rate' => $artisan->orders_count > 0 ?
                round(($artisan->completed_orders_count / $artisan->orders_count) * 100, 2) : 0
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'artisan' => $artisan,
                'recent_orders' => $recentOrders,
                'statistics' => $stats
            ],
            'message' => 'Artisan profile retrieved successfully'
        ]);
    }

    /**
     * Update the specified artisan (Admin only)
     * PUT /api/artisans/{id}
     */
    public function update(Request $request, $id)
    {
        $artisan = Supplier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone_number' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('suppliers')->ignore($artisan->id)
            ],
            'location' => 'sometimes|string|max:255',
            'pin' => 'sometimes|string|min:4|max:6',
            'category_id' => 'sometimes|exists:categories,id',
            'center_id' => 'sometimes|exists:centers,id',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updateData = $request->only([
            'name', 'phone_number', 'location', 'category_id', 'center_id', 'is_active'
        ]);

        // Hash the PIN if provided
        if ($request->has('pin')) {
            $updateData['pin'] = Hash::make($request->pin);
        }

        $artisan->update($updateData);
        $artisan->load(['category', 'center']);

        return response()->json([
            'status' => 'success',
            'data' => $artisan,
            'message' => 'Artisan updated successfully'
        ]);
    }

    /**
     * Toggle artisan active status (Admin only)
     * PATCH /api/artisans/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        $artisan = Supplier::findOrFail($id);
        $artisan->is_active = !$artisan->is_active;
        $artisan->save();

        $status = $artisan->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'status' => 'success',
            'data' => $artisan,
            'message' => "Artisan {$status} successfully"
        ]);
    }

    /**
     * Get available artisans for order allocation
     * GET /api/artisans/available/{categoryId}
     */
    public function getAvailableForAllocation($categoryId)
    {
        $artisans = Supplier::with(['center'])
            ->active()
            ->where('category_id', $categoryId)
            ->withCount(['orders' => function($query) {
                $query->whereIn('status', ['assigned_to_artisan', 'accepted_by_artisan', 'in_production']);
            }])
            ->orderBy('orders_count') // Prioritize artisans with fewer active orders
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $artisans,
            'message' => 'Available artisans for allocation retrieved successfully'
        ]);
    }

    /**
     * Get artisan orders
     * GET /api/artisans/{id}/orders
     */
    public function getOrders($id, Request $request)
    {
        $artisan = Supplier::findOrFail($id);

        $query = Order::with(['customer', 'items.commodity'])
            ->where('supplier_id', $id)
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => [
                'artisan' => $artisan,
                'orders' => $orders
            ],
            'message' => 'Artisan orders retrieved successfully'
        ]);
    }

    /**
     * Get artisan statistics for admin dashboard
     * GET /api/artisans/statistics
     */
    public function statistics()
    {
        $stats = [
            'total_artisans' => Supplier::count(),
            'active_artisans' => Supplier::active()->count(),
            'inactive_artisans' => Supplier::where('is_active', false)->count(),
            'artisans_by_category' => Supplier::join('categories', 'suppliers.category_id', '=', 'categories.id')
                ->selectRaw('categories.category_name as category_name, COUNT(*) as count')
                ->groupBy('categories.id', 'categories.category_name')
                ->get(),
            'artisans_by_center' => Supplier::join('centers', 'suppliers.center_id', '=', 'centers.id')
                ->selectRaw('centers.name as center_name, COUNT(*) as count')
                ->groupBy('centers.id', 'centers.name')
                ->get(),
            'top_performers' => Supplier::withCount(['completedOrders'])
                ->orderBy('completed_orders_count', 'desc')
                ->limit(10)
                ->get(),
            'total_commissions_paid' => Order::sum('artisan_commission')
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Artisan statistics retrieved successfully'
        ]);
    }

    /**
     * Verify artisan PIN for login/authentication
     * POST /api/artisans/{id}/verify-pin
     */
    public function verifyPin(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN is required',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $artisan = Supplier::active()->findOrFail($id);

        if (!Hash::check($request->pin, $artisan->pin)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid PIN'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $artisan->load(['category', 'center']);

        return response()->json([
            'status' => 'success',
            'data' => $artisan,
            'message' => 'PIN verified successfully'
        ]);
    }

    /**
     * Update artisan PIN
     * PATCH /api/artisans/{id}/update-pin
     */
    public function updatePin(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'current_pin' => 'required|string',
            'new_pin' => 'required|string|min:4|max:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $artisan = Supplier::findOrFail($id);

        if (!Hash::check($request->current_pin, $artisan->pin)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current PIN is incorrect'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $artisan->pin = Hash::make($request->new_pin);
        $artisan->save();

        return response()->json([
            'status' => 'success',
            'message' => 'PIN updated successfully'
        ]);
    }

    /**
     * Remove the specified artisan (Soft delete - set inactive)
     * DELETE /api/artisans/{id}
     */
    public function destroy($id)
    {
        $artisan = Supplier::findOrFail($id);

        // Check if artisan has active orders
        $activeOrders = $artisan->orders()
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->count();

        if ($activeOrders > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot deactivate artisan with active orders'
            ], Response::HTTP_CONFLICT);
        }

        // Instead of hard delete, set as inactive
        $artisan->is_active = false;
        $artisan->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Artisan deactivated successfully'
        ]);
    }
}