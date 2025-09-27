<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a paginated listing of orders
     * GET /api/orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'supplier', 'items', 'payments', 'delivery']);

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('customer_id')) {
            $query->byCustomer($request->customer_id);
        }

        if ($request->has('supplier_id')) {
            $query->bySupplier($request->supplier_id);
        }

        $orders = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $orders,
            'message' => 'Orders retrieved successfully'
        ]);
    }

    /**
     * Store a new order
     * POST /api/orders
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'customer_notes' => 'nullable|string',
            'items' => 'array|required',
            'items.*.commodity_id' => 'required|exists:commodities,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $orderData = $request->only(['supplier_id', 'customer_notes', 'total_amount']);
        $orderData['order_number'] = Order::generateOrderNumber();
        $orderData['user_id'] = $request->customer_id;


        $order = Order::create($orderData);

        // Attach order items
        foreach ($request->items as $item) {
            $order->items()->create([
                'commodity_id' => $item['commodity_id'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'total_price'  => $item['quantity'] * $item['unit_price'],
            ]);

        }

        // Calculate deposit & commissions
        $order->calculateDepositAmount();
        $order->calculateCommissions();

        $order->load(['customer', 'items']);

        return response()->json([
            'status' => 'success',
            'data' => $order,
            'message' => 'Order created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display order details
     * GET /api/orders/{id}
     */
    public function show($id)
    {
        $order = Order::with(['customer', 'supplier', 'items', 'payments', 'delivery'])
                      ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $order,
            'message' => 'Order details retrieved successfully'
        ]);
    }

    /**
     * Update order
     * PUT /api/orders/{id}
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'status' => 'sometimes|in:' . implode(',', [
                Order::STATUS_PENDING,
                Order::STATUS_ASSIGNED_TO_ARTISAN,
                Order::STATUS_ACCEPTED_BY_ARTISAN,
                Order::STATUS_DEPOSIT_REQUESTED,
                Order::STATUS_DEPOSIT_PAID,
                Order::STATUS_INGREDIENTS_PROVIDED,
                Order::STATUS_IN_PRODUCTION,
                Order::STATUS_PRODUCTION_COMPLETED,
                Order::STATUS_BALANCE_REQUESTED,
                Order::STATUS_BALANCE_PAID,
                Order::STATUS_READY_FOR_DELIVERY,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
            ]),
            'admin_notes' => 'nullable|string',
            'artisan_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->update($request->only([
            'supplier_id',
            'status',
            'admin_notes',
            'artisan_notes'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => $order,
            'message' => 'Order updated successfully'
        ]);
    }

    /**
     * Delete an order
     * DELETE /api/orders/{id}
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully'
        ]);
    }

    /**
     * Get order statistics
     * GET /api/orders/statistics
     */
    public function statistics()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::byStatus(Order::STATUS_PENDING)->count(),
            'completed_orders' => Order::byStatus(Order::STATUS_DELIVERED)->count(),
            'cancelled_orders' => Order::byStatus(Order::STATUS_CANCELLED)->count(),
            'total_revenue' => Order::where('status', Order::STATUS_DELIVERED)->sum('total_amount'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Order statistics retrieved successfully'
        ]);
    }

    /**
     * Assign order to artisan
     * PATCH /api/orders/{id}/assign
     */
    public function assignToArtisan(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if (!$order->canBeAssigned()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order cannot be assigned'
            ], Response::HTTP_BAD_REQUEST);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'assigned_by' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->update([
            'supplier_id' => $request->supplier_id,
            'assigned_by' => $request->assigned_by,
            'status' => Order::STATUS_ASSIGNED_TO_ARTISAN,
            'assigned_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $order,
            'message' => 'Order assigned to artisan successfully'
        ]);
    }
}
