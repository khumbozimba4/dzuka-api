<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class IngredientController extends Controller
{
    /**
     * Display a listing of ingredients with pagination and filtering
     * GET /api/ingredients
     */
    public function index(Request $request)
    {
        $query = Ingredient::query();

        // Filter by active status
        if ($request->has('active')) {
            if ($request->boolean('active')) {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        // Filter by low stock
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Order by name by default
        $query->orderBy('name');

        $ingredients = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $ingredients,
            'message' => 'Ingredients retrieved successfully'
        ]);
    }

    /**
     * Get ingredients for dropdown/selection (simplified data)
     * GET /api/ingredients/list
     */
    public function list()
    {
        $ingredients = Ingredient::active()
            ->select('id', 'name', 'unit_of_measurement', 'cost_per_unit')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $ingredients,
            'message' => 'Ingredient list retrieved successfully'
        ]);
    }

    /**
     * Get low stock ingredients for alerts
     * GET /api/ingredients/low-stock
     */
    public function lowStock()
    {
        $ingredients = Ingredient::lowStock()
            ->active()
            ->select('id', 'name', 'current_stock', 'minimum_stock_level', 'unit_of_measurement')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $ingredients,
            'count' => $ingredients->count(),
            'message' => 'Low stock ingredients retrieved successfully'
        ]);
    }

    /**
     * Store a newly created ingredient
     * POST /api/ingredients
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:ingredients,name',
            'description' => 'nullable|string',
            'unit_of_measurement' => 'required|string|max:50',
            'cost_per_unit' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock_level' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $ingredient = Ingredient::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $ingredient,
            'message' => 'Ingredient created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified ingredient
     * GET /api/ingredients/{id}
     */
    public function show($id)
    {
        $ingredient = Ingredient::with(['products' => function($query) {
            $query->active()->select('commodities.id', 'commodities.name', 'commodities.selling_price');
        }])->findOrFail($id);

        // Add stock status
        $ingredient->stock_status = $ingredient->isLowStock() ? 'low' : 'adequate';

        return response()->json([
            'status' => 'success',
            'data' => $ingredient,
            'message' => 'Ingredient details retrieved successfully'
        ]);
    }

    /**
     * Update the specified ingredient
     * PUT /api/ingredients/{id}
     */
    public function update(Request $request, $id)
    {
        $ingredient = Ingredient::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:ingredients,name,' . $id,
            'description' => 'nullable|string',
            'unit_of_measurement' => 'sometimes|string|max:50',
            'cost_per_unit' => 'sometimes|numeric|min:0',
            'current_stock' => 'sometimes|numeric|min:0',
            'minimum_stock_level' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $ingredient->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $ingredient,
            'message' => 'Ingredient updated successfully'
        ]);
    }

    /**
     * Add stock to ingredient
     * POST /api/ingredients/{id}/add-stock
     */
    public function addStock(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
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

        $ingredient = Ingredient::findOrFail($id);
        $oldStock = $ingredient->current_stock;

        $ingredient->addStock($request->quantity);

        return response()->json([
            'status' => 'success',
            'data' => [
                'ingredient' => $ingredient,
                'old_stock' => $oldStock,
                'added_quantity' => $request->quantity,
                'new_stock' => $ingredient->current_stock
            ],
            'message' => 'Stock added successfully'
        ]);
    }

    /**
     * Reduce stock from ingredient
     * POST /api/ingredients/{id}/reduce-stock
     */
    public function reduceStock(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
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

        $ingredient = Ingredient::findOrFail($id);

        if ($ingredient->current_stock < $request->quantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient stock available'
            ], Response::HTTP_BAD_REQUEST);
        }

        $oldStock = $ingredient->current_stock;
        $ingredient->reduceStock($request->quantity);

        return response()->json([
            'status' => 'success',
            'data' => [
                'ingredient' => $ingredient,
                'old_stock' => $oldStock,
                'reduced_quantity' => $request->quantity,
                'new_stock' => $ingredient->current_stock,
                'is_low_stock' => $ingredient->isLowStock()
            ],
            'message' => 'Stock reduced successfully'
        ]);
    }

    /**
     * Toggle ingredient active status
     * PATCH /api/ingredients/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->is_active = !$ingredient->is_active;
        $ingredient->save();

        $status = $ingredient->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'status' => 'success',
            'data' => $ingredient,
            'message' => "Ingredient {$status} successfully"
        ]);
    }

    /**
     * Get ingredient statistics for dashboard
     * GET /api/ingredients/statistics
     */
    public function statistics()
    {
        $stats = [
            'total_ingredients' => Ingredient::count(),
            'active_ingredients' => Ingredient::active()->count(),
            'low_stock_count' => Ingredient::lowStock()->active()->count(),
            'total_stock_value' => Ingredient::active()
                ->selectRaw('SUM(current_stock * cost_per_unit) as total')
                ->first()
                ->total ?? 0,
            'average_cost_per_unit' => Ingredient::active()->avg('cost_per_unit')
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Ingredient statistics retrieved successfully'
        ]);
    }

    /**
     * Remove the specified ingredient (soft delete - set inactive)
     * DELETE /api/ingredients/{id}
     */
    public function destroy($id)
    {
        $ingredient = Ingredient::findOrFail($id);

        // Check if ingredient is being used in any active commodities
        $usedInProducts = $ingredient->products()->active()->exists();

        if ($usedInProducts) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete ingredient. It is currently used in active products.'
            ], Response::HTTP_CONFLICT);
        }

        // Set as inactive instead of hard delete
        $ingredient->is_active = false;
        $ingredient->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Ingredient deactivated successfully'
        ]);
    }
}