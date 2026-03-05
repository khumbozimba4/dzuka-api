<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Ingredient;
use App\Models\WarehouseTransfer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses with pagination and filtering
     * GET /api/warehouses
     */
    public function index(Request $request)
    {
        $query = Warehouse::query();

        // Filter by active status
        if ($request->has('active')) {
            if ($request->boolean('active')) {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        // Order by name by default
        $query->orderBy('name');

        $warehouses = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $warehouses,
            'message' => 'Warehouses retrieved successfully'
        ]);
    }

    /**
     * Get warehouses for dropdown/selection (simplified data)
     * GET /api/warehouses/list
     */
    public function list()
    {
        $warehouses = Warehouse::active()
            ->select('id', 'name', 'code', 'type', 'location')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $warehouses,
            'message' => 'Warehouse list retrieved successfully'
        ]);
    }

    /**
     * Store a newly created warehouse
     * POST /api/warehouses
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'type' => 'required|in:main,branch,storage,production',
            'storage_capacity' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $warehouse = Warehouse::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $warehouse,
            'message' => 'Warehouse created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified warehouse with stock information
     * GET /api/warehouses/{id}
     */
    public function show($id)
    {
        $warehouse = Warehouse::with(['ingredients' => function($query) {
            $query->select('ingredients.id', 'ingredients.name', 'ingredients.unit_of_measurement', 'ingredients.cost_per_unit')
                  ->where('ingredients.is_active', true);
        }])->findOrFail($id);

        // Add calculated attributes
        $warehouse->total_stock_value = $warehouse->total_stock_value;
        $warehouse->utilization_percentage = $warehouse->utilization_percentage;
        $warehouse->ingredient_count = $warehouse->ingredients->count();

        // Get low stock count
        $warehouse->low_stock_count = $warehouse->ingredients->filter(function($ingredient) {
            return $ingredient->pivot->quantity <= $ingredient->pivot->minimum_stock_level;
        })->count();

        return response()->json([
            'status' => 'success',
            'data' => $warehouse,
            'message' => 'Warehouse details retrieved successfully'
        ]);
    }

    /**
     * Update the specified warehouse
     * PUT /api/warehouses/{id}
     */
    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:warehouses,code,' . $id,
            'description' => 'nullable|string',
            'location' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'type' => 'sometimes|in:main,branch,storage,production',
            'storage_capacity' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $warehouse->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $warehouse,
            'message' => 'Warehouse updated successfully'
        ]);
    }

    /**
     * Get stock levels for all ingredients in a warehouse
     * GET /api/warehouses/{id}/stock
     */
    public function getStock($id)
    {
        $warehouse = Warehouse::with(['ingredients' => function($query) {
            $query->where('ingredients.is_active', true)
                  ->orderBy('ingredients.name');
        }])->findOrFail($id);

        $stockData = $warehouse->ingredients->map(function($ingredient) {
            return [
                'ingredient_id' => $ingredient->id,
                'ingredient_name' => $ingredient->name,
                'unit_of_measurement' => $ingredient->unit_of_measurement,
                'cost_per_unit' => $ingredient->cost_per_unit,
                'quantity' => $ingredient->pivot->quantity,
                'minimum_stock_level' => $ingredient->pivot->minimum_stock_level,
                'maximum_stock_level' => $ingredient->pivot->maximum_stock_level,
                'location_in_warehouse' => $ingredient->pivot->location_in_warehouse,
                'stock_value' => $ingredient->pivot->quantity * $ingredient->cost_per_unit,
                'is_low_stock' => $ingredient->pivot->quantity <= $ingredient->pivot->minimum_stock_level,
                'stock_status' => $this->getStockStatus($ingredient->pivot)
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'warehouse' => $warehouse,
                'stock' => $stockData,
                'total_stock_value' => $stockData->sum('stock_value'),
                'low_stock_count' => $stockData->where('is_low_stock', true)->count()
            ],
            'message' => 'Warehouse stock retrieved successfully'
        ]);
    }

    /**
     * Add or update ingredient stock in warehouse
     * POST /api/warehouses/{id}/stock
     */
    public function addStock(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.001',
            'minimum_stock_level' => 'nullable|numeric|min:0',
            'maximum_stock_level' => 'nullable|numeric|min:0',
            'location_in_warehouse' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $warehouse = Warehouse::findOrFail($id);
        $ingredient = Ingredient::findOrFail($request->ingredient_id);

        DB::beginTransaction();
        try {
            // Check if ingredient already exists in this warehouse
            $existingStock = $warehouse->ingredients()->where('ingredient_id', $ingredient->id)->first();

            if ($existingStock) {
                // Update existing stock
                $newQuantity = $existingStock->pivot->quantity + $request->quantity;
                $warehouse->ingredients()->updateExistingPivot($ingredient->id, [
                    'quantity' => $newQuantity,
                    'minimum_stock_level' => $request->minimum_stock_level ?? $existingStock->pivot->minimum_stock_level,
                    'maximum_stock_level' => $request->maximum_stock_level ?? $existingStock->pivot->maximum_stock_level,
                    'location_in_warehouse' => $request->location_in_warehouse ?? $existingStock->pivot->location_in_warehouse,
                ]);

                $oldQuantity = $existingStock->pivot->quantity;
            } else {
                // Add new ingredient to warehouse
                $warehouse->ingredients()->attach($ingredient->id, [
                    'quantity' => $request->quantity,
                    'minimum_stock_level' => $request->minimum_stock_level ?? $ingredient->minimum_stock_level,
                    'maximum_stock_level' => $request->maximum_stock_level,
                    'location_in_warehouse' => $request->location_in_warehouse,
                ]);

                $oldQuantity = 0;
                $newQuantity = $request->quantity;
            }

            // Update ingredient total stock
            $ingredient->addStock($request->quantity);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'warehouse' => $warehouse,
                    'ingredient' => $ingredient,
                    'old_quantity' => $oldQuantity,
                    'added_quantity' => $request->quantity,
                    'new_quantity' => $newQuantity
                ],
                'message' => 'Stock added successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add stock: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reduce ingredient stock from warehouse
     * POST /api/warehouses/{id}/reduce-stock
     */
    public function reduceStock(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $warehouse = Warehouse::findOrFail($id);
        $ingredient = Ingredient::findOrFail($request->ingredient_id);

        if (!$warehouse->hasSufficientStock($ingredient->id, $request->quantity)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient stock in this warehouse'
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            $existingStock = $warehouse->ingredients()->where('ingredient_id', $ingredient->id)->first();
            $oldQuantity = $existingStock->pivot->quantity;
            $newQuantity = $oldQuantity - $request->quantity;

            $warehouse->ingredients()->updateExistingPivot($ingredient->id, [
                'quantity' => $newQuantity
            ]);

            // Update ingredient total stock
            $ingredient->reduceStock($request->quantity);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'warehouse' => $warehouse,
                    'ingredient' => $ingredient,
                    'old_quantity' => $oldQuantity,
                    'reduced_quantity' => $request->quantity,
                    'new_quantity' => $newQuantity,
                    'is_low_stock' => $newQuantity <= $existingStock->pivot->minimum_stock_level
                ],
                'message' => 'Stock reduced successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reduce stock: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get low stock items in warehouse
     * GET /api/warehouses/{id}/low-stock
     */
    public function getLowStock($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $lowStockItems = $warehouse->ingredients()
            ->wherePivot('quantity', '<=', DB::raw('warehouse_ingredients.minimum_stock_level'))
            ->where('ingredients.is_active', true)
            ->get()
            ->map(function($ingredient) {
                return [
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $ingredient->name,
                    'unit_of_measurement' => $ingredient->unit_of_measurement,
                    'quantity' => $ingredient->pivot->quantity,
                    'minimum_stock_level' => $ingredient->pivot->minimum_stock_level,
                    'deficit' => $ingredient->pivot->minimum_stock_level - $ingredient->pivot->quantity
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $lowStockItems,
            'count' => $lowStockItems->count(),
            'message' => 'Low stock items retrieved successfully'
        ]);
    }

    /**
     * Toggle warehouse active status
     * PATCH /api/warehouses/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->is_active = !$warehouse->is_active;
        $warehouse->save();

        $status = $warehouse->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'status' => 'success',
            'data' => $warehouse,
            'message' => "Warehouse {$status} successfully"
        ]);
    }

    /**
     * Get warehouse statistics for dashboard
     * GET /api/warehouses/statistics
     */
    public function statistics()
    {
        $stats = [
            'total_warehouses' => Warehouse::count(),
            'active_warehouses' => Warehouse::active()->count(),
            'total_stock_value' => Warehouse::active()->get()->sum('total_stock_value'),
            'average_utilization' => Warehouse::active()->get()->avg('utilization_percentage'),
            'warehouses_with_low_stock' => Warehouse::active()->whereHas('ingredients', function($query) {
                $query->whereColumn('warehouse_ingredients.quantity', '<=', 'warehouse_ingredients.minimum_stock_level');
            })->count()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Warehouse statistics retrieved successfully'
        ]);
    }

    /**
     * Remove the specified warehouse (soft delete - set inactive)
     * DELETE /api/warehouses/{id}
     */
    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        // Check if warehouse has stock
        if ($warehouse->ingredients()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete warehouse with existing stock. Please transfer or remove all stock first.'
            ], Response::HTTP_CONFLICT);
        }

        // Set as inactive instead of hard delete
        $warehouse->is_active = false;
        $warehouse->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse deactivated successfully'
        ]);
    }

    /**
     * Helper method to determine stock status
     */
    private function getStockStatus($pivot)
    {
        if ($pivot->quantity == 0) {
            return 'Out of Stock';
        } elseif ($pivot->quantity <= $pivot->minimum_stock_level) {
            return 'Low Stock';
        } elseif ($pivot->maximum_stock_level && $pivot->quantity >= $pivot->maximum_stock_level) {
            return 'Overstocked';
        } else {
            return 'In Stock';
        }
    }
}