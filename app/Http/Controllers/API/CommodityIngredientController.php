<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\CommodityIngredient;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CommodityIngredientController extends Controller
{
    /**
     * Display ingredients for a specific commodity (Bill of Materials)
     * GET /api/commodities/{commodityId}/ingredients
     */
    public function index($commodityId)
    {
        $commodity = Commodity::findOrFail($commodityId);

        $ingredients = $commodity->ingredients()
            ->select('ingredients.*', 'commodity_ingredients.quantity_required', 'commodity_ingredients.cost_per_unit')
            ->get()
            ->map(function ($ingredient) {
                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'unit_of_measurement' => $ingredient->unit_of_measurement,
                    'quantity_required' => $ingredient->pivot->quantity_required,
                    'cost_per_unit' => $ingredient->pivot->cost_per_unit,
                    'total_cost' => $ingredient->pivot->quantity_required * $ingredient->pivot->cost_per_unit,
                    'current_stock' => $ingredient->current_stock,
                    'stock_available' => $ingredient->current_stock >= $ingredient->pivot->quantity_required
                ];
            });

        $totalCost = $ingredients->sum('total_cost');

        return response()->json([
            'status' => 'success',
            'data' => [
                'commodity' => $commodity->only(['id', 'name', 'production_cost']),
                'ingredients' => $ingredients,
                'total_cost' => $totalCost,
                'ingredients_count' => $ingredients->count()
            ],
            'message' => 'Commodity ingredients retrieved successfully'
        ]);
    }

    /**
     * Add ingredient to commodity recipe
     * POST /api/commodities/{commodityId}/ingredients
     */
    public function store(Request $request, $commodityId)
    {
        $commodity = Commodity::findOrFail($commodityId);

        $validator = Validator::make($request->all(), [
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity_required' => 'required|numeric|min:0.001',
            'cost_per_unit' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if ingredient is already attached to this commodity
        if ($commodity->ingredients()->where('ingredient_id', $request->ingredient_id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ingredient is already added to this commodity'
            ], Response::HTTP_CONFLICT);
        }

        $ingredient = Ingredient::findOrFail($request->ingredient_id);

        // Attach ingredient to commodity
        $commodity->ingredients()->attach($request->ingredient_id, [
            'quantity_required' => $request->quantity_required,
            'cost_per_unit' => $request->cost_per_unit
        ]);

        // Recalculate production cost
        $commodity->calculateProductionCost();

        $ingredientData = [
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'unit_of_measurement' => $ingredient->unit_of_measurement,
            'quantity_required' => $request->quantity_required,
            'cost_per_unit' => $request->cost_per_unit,
            'total_cost' => $request->quantity_required * $request->cost_per_unit
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'ingredient' => $ingredientData,
                'commodity_production_cost' => $commodity->fresh()->production_cost
            ],
            'message' => 'Ingredient added to commodity successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display specific commodity ingredient relationship
     * GET /api/commodities/{commodityId}/ingredients/{ingredientId}
     */
    public function show($commodityId, $ingredientId)
    {
        $commodity = Commodity::findOrFail($commodityId);
        $ingredient = $commodity->ingredients()
            ->where('ingredients.id', $ingredientId)
            ->first();

        if (!$ingredient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ingredient not found for this commodity'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'commodity_id' => $commodityId,
            'commodity_name' => $commodity->name,
            'ingredient' => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit_of_measurement' => $ingredient->unit_of_measurement,
                'current_stock' => $ingredient->current_stock,
                'quantity_required' => $ingredient->pivot->quantity_required,
                'cost_per_unit' => $ingredient->pivot->cost_per_unit,
                'total_cost' => $ingredient->pivot->quantity_required * $ingredient->pivot->cost_per_unit
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Commodity ingredient details retrieved successfully'
        ]);
    }

    /**
     * Update ingredient requirements for a commodity
     * PUT /api/commodities/{commodityId}/ingredients/{ingredientId}
     */
    public function update(Request $request, $commodityId, $ingredientId)
    {
        $commodity = Commodity::findOrFail($commodityId);

        // Check if the ingredient is attached to this commodity
        $exists = $commodity->ingredients()->where('ingredient_id', $ingredientId)->exists();

        if (!$exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ingredient not found for this commodity'
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'quantity_required' => 'required|numeric|min:0.001',
            'cost_per_unit' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Update the pivot data
        $commodity->ingredients()->updateExistingPivot($ingredientId, [
            'quantity_required' => $request->quantity_required,
            'cost_per_unit' => $request->cost_per_unit
        ]);

        // Recalculate production cost
        $commodity->calculateProductionCost();

        $ingredient = Ingredient::find($ingredientId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'ingredient' => [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'quantity_required' => $request->quantity_required,
                    'cost_per_unit' => $request->cost_per_unit,
                    'total_cost' => $request->quantity_required * $request->cost_per_unit
                ],
                'commodity_production_cost' => $commodity->fresh()->production_cost
            ],
            'message' => 'Commodity ingredient updated successfully'
        ]);
    }

    /**
     * Remove ingredient from commodity recipe
     * DELETE /api/commodities/{commodityId}/ingredients/{ingredientId}
     */
    public function destroy($commodityId, $ingredientId)
    {
        $commodity = Commodity::findOrFail($commodityId);

        // Check if the ingredient is attached to this commodity
        $exists = $commodity->ingredients()->where('ingredient_id', $ingredientId)->exists();

        if (!$exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ingredient not found for this commodity'
            ], Response::HTTP_NOT_FOUND);
        }

        // Detach the ingredient
        $commodity->ingredients()->detach($ingredientId);

        // Recalculate production cost
        $commodity->calculateProductionCost();

        return response()->json([
            'status' => 'success',
            'data' => [
                'commodity_production_cost' => $commodity->fresh()->production_cost
            ],
            'message' => 'Ingredient removed from commodity successfully'
        ]);
    }

    /**
     * Bulk update ingredients for a commodity
     * PUT /api/commodities/{commodityId}/ingredients/bulk
     */
    public function bulkUpdate(Request $request, $commodityId)
    {
        $commodity = Commodity::findOrFail($commodityId);

        $validator = Validator::make($request->all(), [
            'ingredients' => 'required|array|min:1',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity_required' => 'required|numeric|min:0.001',
            'ingredients.*.cost_per_unit' => 'required|numeric|min:0'
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
            // Remove all existing ingredients
            $commodity->ingredients()->detach();

            // Add new ingredients
            foreach ($request->ingredients as $ingredientData) {
                $commodity->ingredients()->attach($ingredientData['ingredient_id'], [
                    'quantity_required' => $ingredientData['quantity_required'],
                    'cost_per_unit' => $ingredientData['cost_per_unit']
                ]);
            }

            // Recalculate production cost
            $commodity->calculateProductionCost();

            DB::commit();

            // Get updated ingredients list
            $updatedIngredients = $commodity->fresh()->ingredients()
                ->select('ingredients.*', 'commodity_ingredients.quantity_required', 'commodity_ingredients.cost_per_unit')
                ->get()
                ->map(function ($ingredient) {
                    return [
                        'id' => $ingredient->id,
                        'name' => $ingredient->name,
                        'unit_of_measurement' => $ingredient->unit_of_measurement,
                        'quantity_required' => $ingredient->pivot->quantity_required,
                        'cost_per_unit' => $ingredient->pivot->cost_per_unit,
                        'total_cost' => $ingredient->pivot->quantity_required * $ingredient->pivot->cost_per_unit
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'ingredients' => $updatedIngredients,
                    'total_cost' => $updatedIngredients->sum('total_cost'),
                    'commodity_production_cost' => $commodity->production_cost
                ],
                'message' => 'Commodity ingredients updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update commodity ingredients'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check ingredient availability for production
     * GET /api/commodities/{commodityId}/ingredients/availability
     */
    public function checkAvailability($commodityId, Request $request)
    {
        $commodity = Commodity::findOrFail($commodityId);
        $quantity = $request->get('quantity', 1); // Number of units to produce

        $ingredients = $commodity->ingredients()
            ->select('ingredients.*', 'commodity_ingredients.quantity_required', 'commodity_ingredients.cost_per_unit')
            ->get()
            ->map(function ($ingredient) use ($quantity) {
                $requiredQuantity = $ingredient->pivot->quantity_required * $quantity;
                $isAvailable = $ingredient->current_stock >= $requiredQuantity;
                $shortage = $isAvailable ? 0 : $requiredQuantity - $ingredient->current_stock;

                return [
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $ingredient->name,
                    'unit_of_measurement' => $ingredient->unit_of_measurement,
                    'current_stock' => $ingredient->current_stock,
                    'required_per_unit' => $ingredient->pivot->quantity_required,
                    'total_required' => $requiredQuantity,
                    'is_available' => $isAvailable,
                    'shortage' => $shortage,
                    'cost_per_unit' => $ingredient->pivot->cost_per_unit,
                    'total_cost' => $requiredQuantity * $ingredient->pivot->cost_per_unit
                ];
            });

        $canProduce = $ingredients->every('is_available');
        $totalCost = $ingredients->sum('total_cost');
        $shortageCount = $ingredients->where('is_available', false)->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'commodity' => $commodity->only(['id', 'name']),
                'production_quantity' => $quantity,
                'can_produce' => $canProduce,
                'total_production_cost' => $totalCost,
                'ingredients' => $ingredients,
                'shortage_summary' => [
                    'ingredients_with_shortage' => $shortageCount,
                    'total_ingredients' => $ingredients->count()
                ]
            ],
            'message' => 'Ingredient availability checked successfully'
        ]);
    }

    /**
     * Calculate production cost for specific quantity
     * GET /api/commodities/{commodityId}/ingredients/cost-calculation
     */
    public function calculateCost($commodityId, Request $request)
    {
        $commodity = Commodity::findOrFail($commodityId);
        $quantity = $request->get('quantity', 1);

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $ingredients = $commodity->ingredients()
            ->select('ingredients.name', 'commodity_ingredients.quantity_required', 'commodity_ingredients.cost_per_unit')
            ->get()
            ->map(function ($ingredient) use ($quantity) {
                $totalQuantity = $ingredient->pivot->quantity_required * $quantity;
                $totalCost = $totalQuantity * $ingredient->pivot->cost_per_unit;

                return [
                    'ingredient_name' => $ingredient->name,
                    'unit_cost' => $ingredient->pivot->cost_per_unit,
                    'quantity_per_unit' => $ingredient->pivot->quantity_required,
                    'total_quantity' => $totalQuantity,
                    'total_cost' => $totalCost
                ];
            });

        $totalProductionCost = $ingredients->sum('total_cost');
        $costPerUnit = $commodity->production_cost;
        $totalSellingPrice = $commodity->selling_price * $quantity;
        $totalProfit = $totalSellingPrice - $totalProductionCost;
        $profitMargin = $totalSellingPrice > 0 ? (($totalProfit / $totalSellingPrice) * 100) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'commodity' => $commodity->only(['id', 'name', 'selling_price']),
                'production_quantity' => $quantity,
                'cost_breakdown' => $ingredients,
                'cost_summary' => [
                    'cost_per_unit' => $costPerUnit,
                    'total_production_cost' => $totalProductionCost,
                    'selling_price_per_unit' => $commodity->selling_price,
                    'total_selling_price' => $totalSellingPrice,
                    'profit_per_unit' => $commodity->selling_price - $costPerUnit,
                    'total_profit' => $totalProfit,
                    'profit_margin_percentage' => round($profitMargin, 2)
                ]
            ],
            'message' => 'Production cost calculated successfully'
        ]);
    }
}