<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\IngredientAllocation;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a paginated listing of orders
     * GET /api/orders
     */
    public function index(Request $request)
    {
        $query = Order::with([
            'customer',
            'supplier',
            'items.commodity:id,name',
            'payments',
            'delivery'
        ]);
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
        $order = Order::with(['customer',
         'supplier',
         'items.commodity:id,name',
         'payments',
          'delivery'])
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
     * Record deposit payment by admin
     * POST /api/orders/{id}/deposit-payment
     */
    public function recordDepositPayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Check if order is in the correct status
        if ($order->status !== Order::STATUS_DEPOSIT_REQUESTED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order is not in deposit requested status'
            ], Response::HTTP_BAD_REQUEST);
        }

        $validator = Validator::make($request->all(), [
            'deposit_amount' => 'required|numeric|min:0|max:' . $order->total_amount,
            'payment_method' => 'required|string|in:cash,bank_transfer,mobile_money,card',
            'payment_reference' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'customer_id' => $order->user_id,        // correct column
            'amount' => $request->deposit_amount,
            'payment_type' => Payment::TYPE_DEPOSIT, // 'deposit'
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'status' => Payment::STATUS_COMPLETED,   // correct column
            'paid_at' => now(),
        ]);

        // Update order
        $order->update([
            'deposit_amount' => $request->deposit_amount,
            'balance_amount' => $order->total_amount - $request->deposit_amount,
            'status' => Order::STATUS_DEPOSIT_PAID,
            'deposit_paid_at' => now(),
            'admin_notes' => $request->admin_notes
        ]);

        $order->load(['customer', 'supplier', 'payments']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => $order,
                'payment' => $payment
            ],
            'message' => 'Deposit payment recorded successfully'
        ]);
    }


    /**
     * Record balance payment by admin
     * POST /api/orders/{id}/balance-payment
     */
    public function recordBalancePayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Check if order is in the correct status
        if ($order->status !== Order::STATUS_BALANCE_REQUESTED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order is not in balance requested status'
            ], Response::HTTP_BAD_REQUEST);
        }

        $validator = Validator::make($request->all(), [
            'balance_amount' => 'required|numeric|min:0|max:' . $order->balance_amount,
            'payment_method' => 'required|string|in:cash,bank_transfer,mobile_money,card',
            'payment_reference' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string',
            'paid_by_admin_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'customer_id' => $order->user_id,
            'amount' => $request->balance_amount,
            'payment_type' => Payment::TYPE_BALANCE,   // 'balance'
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'status' => Payment::STATUS_COMPLETED,     // use 'status', not 'payment_status'
            'paid_at' => now(),
        ]);


        // Update order
        $order->update([
            'status' => Order::STATUS_BALANCE_PAID,
            'admin_notes' => $request->admin_notes
        ]);

        $order->load(['customer', 'supplier', 'payments']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => $order,
                'payment' => $payment
            ],
            'message' => 'Balance payment recorded successfully'
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


    public function allocateIngredients(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Check if order is in the correct status for ingredient allocation
        $allowedStatuses = [
            Order::STATUS_DEPOSIT_PAID,
            Order::STATUS_ACCEPTED_BY_ARTISAN,
            Order::STATUS_ASSIGNED_TO_ARTISAN,
            Order::STATUS_IN_PRODUCTION
        ];

        if (!in_array($order->status, $allowedStatuses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order is not in a valid status for ingredient allocation'
            ], Response::HTTP_BAD_REQUEST);
        }

        $validator = Validator::make($request->all(), [
            'allocations' => 'required|array|min:1',
            'allocations.*.ingredient_id' => 'required|exists:ingredients,id',
            'allocations.*.quantity_allocated' => 'required|numeric|min:0.001',
            'allocations.*.cost_per_unit' => 'required|numeric|min:0',
            'allocations.*.allocated_by' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();

        try {
            $createdAllocations = [];
            $totalAllocationCost = 0;

            foreach ($request->allocations as $allocationData) {
                // Get ingredient and check stock
                $ingredient = Ingredient::findOrFail($allocationData['ingredient_id']);

                if ($ingredient->current_stock < $allocationData['quantity_allocated']) {
                    throw new \Exception("Insufficient stock for ingredient: {$ingredient->name}. Available: {$ingredient->current_stock}, Requested: {$allocationData['quantity_allocated']}");
                }

                // Check if ingredient is already allocated for this order
                $existingAllocation = IngredientAllocation::where('order_id', $order->id)
                    ->where('ingredient_id', $allocationData['ingredient_id'])
                    ->first();

                if ($existingAllocation) {
                    throw new \Exception("Ingredient {$ingredient->name} is already allocated to this order");
                }

                // Create allocation record
                $allocation = IngredientAllocation::create([
                    'order_id' => $order->id,
                    'ingredient_id' => $allocationData['ingredient_id'],
                    'quantity_allocated' => $allocationData['quantity_allocated'],
                    'cost_per_unit' => $allocationData['cost_per_unit'],
                    'total_cost' => $allocationData['quantity_allocated'] * $allocationData['cost_per_unit'], // Add this
                    'allocated_by' => $allocationData['allocated_by'],
                    'allocated_at' => now()
                ]);

                // Calculate total cost
                $allocation->calculateTotalCost();

                // Reduce ingredient stock
                $ingredient->reduceStock($allocationData['quantity_allocated']);

                $totalAllocationCost += $allocation->total_cost;
                $createdAllocations[] = $allocation->load(['ingredient', 'allocatedBy']);
            }

            // Update order status if it's the first ingredient allocation
            if ($order->status === Order::STATUS_DEPOSIT_PAID ||
                $order->status === Order::STATUS_ACCEPTED_BY_ARTISAN) {
                $order->update([
                    'status' => Order::STATUS_INGREDIENTS_PROVIDED
                ]);
            }

            DB::commit();

            $order->load(['customer', 'supplier', 'items.commodity', 'ingredientAllocations.ingredient']);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => $order,
                    'allocations' => $createdAllocations,
                    'total_allocation_cost' => $totalAllocationCost
                ],
                'message' => 'Ingredients allocated successfully'
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get ingredient allocations for an order
     * GET /api/orders/{id}/ingredient-allocations
     */
    public function getIngredientAllocations($id)
    {
        $order = Order::findOrFail($id);

        $allocations = IngredientAllocation::where('order_id', $order->id)
            ->with(['ingredient', 'allocatedBy'])
            ->orderBy('allocated_at', 'desc')
            ->get();

        $totalCost = $allocations->sum('total_cost');

        return response()->json([
            'status' => 'success',
            'data' => [
                'allocations' => $allocations,
                'total_cost' => $totalCost,
                'count' => $allocations->count()
            ],
            'message' => 'Ingredient allocations retrieved successfully'
        ]);
    }

    /**
     * Update ingredient allocation
     * PUT /api/orders/{orderId}/ingredient-allocations/{allocationId}
     */
    public function updateIngredientAllocation(Request $request, $orderId, $allocationId)
    {
        $order = Order::findOrFail($orderId);
        $allocation = IngredientAllocation::where('order_id', $order->id)
            ->findOrFail($allocationId);

        $validator = Validator::make($request->all(), [
            'quantity_allocated' => 'sometimes|numeric|min:0.001',
            'cost_per_unit' => 'sometimes|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();

        try {
            $ingredient = $allocation->ingredient;
            $oldQuantity = $allocation->quantity_allocated;

            // If quantity is being changed, adjust stock
            if ($request->has('quantity_allocated') && $request->quantity_allocated != $oldQuantity) {
                $quantityDifference = $request->quantity_allocated - $oldQuantity;

                // Check if there's enough stock for increase
                if ($quantityDifference > 0 && $ingredient->current_stock < $quantityDifference) {
                    throw new \Exception("Insufficient stock. Available: {$ingredient->current_stock}, Additional needed: {$quantityDifference}");
                }

                // Adjust ingredient stock
                if ($quantityDifference > 0) {
                    $ingredient->reduceStock($quantityDifference);
                } else {
                    $ingredient->addStock(abs($quantityDifference));
                }
            }

            // Update allocation
            $allocation->update($request->only(['quantity_allocated', 'cost_per_unit']));
            $allocation->calculateTotalCost();

            DB::commit();

            $allocation->load(['ingredient', 'allocatedBy']);

            return response()->json([
                'status' => 'success',
                'data' => $allocation,
                'message' => 'Ingredient allocation updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Delete ingredient allocation
     * DELETE /api/orders/{orderId}/ingredient-allocations/{allocationId}
     */
    public function deleteIngredientAllocation($orderId, $allocationId)
    {
        $order = Order::findOrFail($orderId);
        $allocation = IngredientAllocation::where('order_id', $order->id)
            ->findOrFail($allocationId);

        DB::beginTransaction();

        try {
            // Return ingredient stock
            $allocation->ingredient->addStock($allocation->quantity_allocated);

            // Delete allocation
            $allocation->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Ingredient allocation deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    /**
 * Get required ingredients for order production
 * GET /api/orders/{id}/required-ingredients
 */
public function getRequiredIngredients($id)
{
    $order = Order::with(['items.commodity.ingredients'])->findOrFail($id);

    $requiredIngredients = [];

    foreach ($order->items as $orderItem) {
        $commodity = $orderItem->commodity;

        if (!$commodity) continue;

        foreach ($commodity->ingredients as $ingredient) {
            $requiredQuantity = $ingredient->pivot->quantity_required * $orderItem->quantity;
            $costPerUnit = $ingredient->pivot->cost_per_unit;

            // If ingredient already in array, add to quantity
            if (isset($requiredIngredients[$ingredient->id])) {
                $requiredIngredients[$ingredient->id]['total_required'] += $requiredQuantity;
                $requiredIngredients[$ingredient->id]['total_cost'] =
                    $requiredIngredients[$ingredient->id]['total_required'] * $costPerUnit;
            } else {
                $requiredIngredients[$ingredient->id] = [
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $ingredient->name,
                    'unit_of_measurement' => $ingredient->unit_of_measurement,
                    'current_stock' => $ingredient->current_stock,
                    'quantity_per_unit' => $ingredient->pivot->quantity_required,
                    'total_required' => $requiredQuantity,
                    'cost_per_unit' => $costPerUnit,
                    'total_cost' => $requiredQuantity * $costPerUnit,
                    'is_sufficient' => $ingredient->current_stock >= $requiredQuantity
                ];
            }
        }
    }

    $totalCost = array_sum(array_column($requiredIngredients, 'total_cost'));
    $hasSufficientStock = collect($requiredIngredients)->every('is_sufficient');

    return response()->json([
        'status' => 'success',
        'data' => [
            'order' => $order->only(['id', 'order_number', 'status']),
            'required_ingredients' => array_values($requiredIngredients),
            'total_cost' => $totalCost,
            'has_sufficient_stock' => $hasSufficientStock,
            'ingredients_count' => count($requiredIngredients)
        ],
        'message' => 'Required ingredients retrieved successfully'
    ]);
}
}
