<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Sector;
use App\Services\CostCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CommodityController extends Controller
{
    protected $costCalculator;

    public function __construct(CostCalculatorService $costCalculator)
    {
        $this->costCalculator = $costCalculator;
    }

    /**
     * Display paginated listing of active commodities
     * GET /api/commodities
     */
    public function index(Request $request)
    {
        $query = Commodity::with(['sector', 'ingredients'])
            ->active()
            ->orderBy('name');

        // Filter by sector if provided
        if ($request->has('sector_id')) {
            $query->bySector($request->sector_id);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $commodities = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $commodities,
            'message' => 'Commodities retrieved successfully'
        ]);
    }

    /**
     * Get commodities grouped by sectors for catalogue display
     * GET /api/commodities/catalogue
     */
    public function catalogue()
    {
        $sectors = Sector::with(['commodities' => function($query) {
            $query->active()
                  ->select('id', 'sector_id', 'name', 'description', 'image', 'selling_price', 'production_time_hours')
                  ->orderBy('name');
        }])
        ->whereHas('commodities', function($query) {
            $query->active();
        })
        ->orderBy('name')
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sectors,
            'message' => 'Product catalogue retrieved successfully'
        ]);
    }

    /**
     * Get commodities by specific sector
     * GET /api/commodities/sector/{sectorId}
     */
    public function getBySector($sectorId)
    {
        $sector = Sector::findOrFail($sectorId);

        $commodities = Commodity::with('ingredients')
            ->active()
            ->bySector($sectorId)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'sector' => $sector,
                'commodities' => $commodities
            ],
            'message' => "Commodities for {$sector->name} sector retrieved successfully"
        ]);
    }

    /**
     * Store a newly created commodity (Admin only)
     * POST /api/commodities
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sector_id' => 'required|exists:sectors,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'selling_price' => 'nullable|numeric|min:0',
            'production_time_hours' => 'required|integer|min:1',
            'ingredients' => 'array',
            'ingredients.*.ingredient_id' => 'required_with:ingredients|exists:ingredients,id',
            'ingredients.*.quantity_required' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.cost_per_unit' => 'required_with:ingredients|numeric|min:0',
            'auto_calculate_costs' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $commodityData = $request->only([
            'sector_id', 'name', 'description', 'production_time_hours'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('commodities', 'public');
            $commodityData['image'] = $imagePath;
        }

        $commodity = Commodity::create($commodityData);

        // Attach ingredients if provided
        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingredient) {
                $commodity->ingredients()->attach($ingredient['ingredient_id'], [
                    'quantity_required' => $ingredient['quantity_required'],
                    'cost_per_unit' => $ingredient['cost_per_unit']
                ]);
            }
        }

        // Auto-calculate all costs based on ingredients (default behavior)
        if ($request->get('auto_calculate_costs', true) && $request->has('ingredients')) {
            try {
                $this->costCalculator->calculateFullCostStructure($commodity);
            } catch (\Exception $e) {
                // If calculation fails, set manual selling price if provided
                if ($request->has('selling_price')) {
                    $commodity->update(['selling_price' => $request->selling_price]);
                }
            }
        } else if ($request->has('selling_price')) {
            // Manual selling price
            $commodity->update(['selling_price' => $request->selling_price]);
            $commodity->calculateProductionCost();
        }

        $commodity->load(['sector', 'ingredients']);

        return response()->json([
            'status' => 'success',
            'data' => $commodity,
            'message' => 'Commodity created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified commodity with full details
     * GET /api/commodities/{id}
     */
    public function show($id)
    {
        $commodity = Commodity::with(['sector', 'ingredients'])
            ->findOrFail($id);

        // Add calculated fields
        $commodity->profit_margin = $commodity->profit_margin;

        return response()->json([
            'status' => 'success',
            'data' => $commodity,
            'message' => 'Commodity details retrieved successfully'
        ]);
    }

    /**
     * Get commodity details for customer view (limited info)
     * GET /api/commodities/{id}/customer
     */
    public function showForCustomer($id)
    {
        $commodity = Commodity::with('sector')
            ->active()
            ->select('id', 'sector_id', 'name', 'description', 'image', 'selling_price', 'production_time_hours')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $commodity,
            'message' => 'Commodity details retrieved successfully'
        ]);
    }

    /**
     * Update the specified commodity (Admin only)
     * PUT /api/commodities/{id}
     */
    public function update(Request $request, $id)
    {
        $commodity = Commodity::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'sector_id' => 'sometimes|exists:sectors,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'selling_price' => 'nullable|numeric|min:0',
            'production_time_hours' => 'sometimes|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'ingredients' => 'array',
            'ingredients.*.ingredient_id' => 'required_with:ingredients|exists:ingredients,id',
            'ingredients.*.quantity_required' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.cost_per_unit' => 'required_with:ingredients|numeric|min:0',
            'auto_calculate_costs' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updateData = $request->only([
            'sector_id', 'name', 'description', 'production_time_hours', 'is_active'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($commodity->image) {
                Storage::disk('public')->delete($commodity->image);
            }
            $imagePath = $request->file('image')->store('commodities', 'public');
            $updateData['image'] = $imagePath;
        }

        $commodity->update($updateData);

        // Update ingredients if provided
        if ($request->has('ingredients')) {
            $commodity->ingredients()->detach();
            foreach ($request->ingredients as $ingredient) {
                $commodity->ingredients()->attach($ingredient['ingredient_id'], [
                    'quantity_required' => $ingredient['quantity_required'],
                    'cost_per_unit' => $ingredient['cost_per_unit']
                ]);
            }

            // Auto-recalculate all costs (default behavior)
            if ($request->get('auto_calculate_costs', true)) {
                try {
                    $this->costCalculator->calculateFullCostStructure($commodity);
                } catch (\Exception $e) {
                    // If calculation fails, use manual price if provided
                    if ($request->has('selling_price')) {
                        $commodity->update(['selling_price' => $request->selling_price]);
                    }
                }
            } else if ($request->has('selling_price')) {
                // Manual selling price
                $commodity->update(['selling_price' => $request->selling_price]);
                $commodity->calculateProductionCost();
            }
        } else if ($request->has('selling_price')) {
            // Update selling price only if no ingredient changes
            $commodity->update(['selling_price' => $request->selling_price]);
        }

        $commodity->load(['sector', 'ingredients']);

        return response()->json([
            'status' => 'success',
            'data' => $commodity,
            'message' => 'Commodity updated successfully'
        ]);
    }

    /**
     * Toggle commodity active status (Admin only)
     * PATCH /api/commodities/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        $commodity = Commodity::findOrFail($id);
        $commodity->is_active = !$commodity->is_active;
        $commodity->save();

        $status = $commodity->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'status' => 'success',
            'data' => $commodity,
            'message' => "Commodity {$status} successfully"
        ]);
    }

    /**
     * Get popular/featured commodities
     * GET /api/commodities/featured
     */
    public function featured()
    {
        // This could be based on order frequency, rating, or manual selection
        // For now, we'll get random active commodities
        $commodities = Commodity::with('sector')
            ->active()
            ->inRandomOrder()
            ->limit(8)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $commodities,
            'message' => 'Featured commodities retrieved successfully'
        ]);
    }

    /**
     * Get commodity statistics for admin dashboard
     * GET /api/commodities/statistics
     */
    public function statistics()
    {
        $stats = [
            'total_commodities' => Commodity::count(),
            'active_commodities' => Commodity::active()->count(),
            'inactive_commodities' => Commodity::where('is_active', false)->count(),
            'commodities_by_sector' => Commodity::join('sectors', 'commodities.sector_id', '=', 'sectors.id')
                ->selectRaw('sectors.name as sector_name, COUNT(*) as count')
                ->groupBy('sectors.id', 'sectors.name')
                ->get(),
            'average_selling_price' => Commodity::active()->avg('selling_price'),
            'average_production_cost' => Commodity::active()->avg('production_cost'),
            'total_raw_materials_cost' => Commodity::active()->sum('raw_materials_cost'),
            'total_direct_labor_cost' => Commodity::active()->sum('direct_labor_cost'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Commodity statistics retrieved successfully'
        ]);
    }

    /**
     * Remove the specified commodity (Soft delete - set inactive)
     * DELETE /api/commodities/{id}
     */
    public function destroy($id)
    {
        $commodity = Commodity::findOrFail($id);

        // Instead of hard delete, set as inactive
        $commodity->is_active = false;
        $commodity->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Commodity deactivated successfully'
        ]);
    }

    // ==================== COST CALCULATION METHODS ====================

    /**
     * Calculate all costs for a specific commodity
     * POST /api/commodities/{id}/calculate-costs
     */
    public function calculateCosts($id)
    {
        try {
            $commodity = Commodity::with('ingredients')->findOrFail($id);

            // Calculate full cost structure
            $updated = $this->costCalculator->calculateFullCostStructure($commodity);

            return response()->json([
                'status' => 'success',
                'message' => 'Costs calculated successfully',
                'data' => $this->costCalculator->getCostReport($updated)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get detailed cost breakdown for a commodity
     * GET /api/commodities/{id}/cost-breakdown
     */
    public function getCostBreakdown($id)
    {
        try {
            $commodity = Commodity::findOrFail($id);
            $breakdown = $commodity->getCostBreakdown();

            return response()->json([
                'status' => 'success',
                'data' => $breakdown,
                'message' => 'Cost breakdown retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get revenue distribution for a commodity
     * GET /api/commodities/{id}/revenue-distribution
     */
    public function getRevenueDistribution($id)
    {
        try {
            $commodity = Commodity::findOrFail($id);
            $distribution = $commodity->getRevenueDistribution();

            return response()->json([
                'status' => 'success',
                'data' => $distribution,
                'message' => 'Revenue distribution retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Compare ingredient costs with stored costs
     * GET /api/commodities/{id}/compare-ingredient-costs
     */
    public function compareIngredientCosts($id)
    {
        try {
            $commodity = Commodity::with('ingredients')->findOrFail($id);
            $comparison = $this->costCalculator->getIngredientCostComparison($commodity);

            return response()->json([
                'status' => 'success',
                'data' => $comparison,
                'message' => 'Ingredient cost comparison retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get cost per production hour
     * GET /api/commodities/{id}/cost-per-hour
     */
    public function getCostPerHour($id)
    {
        try {
            $commodity = Commodity::findOrFail($id);
            $hourlyData = $this->costCalculator->getCostPerHour($commodity);

            return response()->json([
                'status' => 'success',
                'data' => $hourlyData,
                'message' => 'Cost per hour retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update selling price based on desired profit margin
     * PUT /api/commodities/{id}/update-price-by-margin
     */
    public function updatePriceByMargin(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'target_margin_percentage' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $commodity = Commodity::findOrFail($id);
            $targetMargin = $request->target_margin_percentage;

            $oldPrice = $commodity->selling_price;

            // Calculate selling price based on target margin
            $newSellingPrice = $commodity->production_cost / (1 - ($targetMargin / 100));

            $commodity->update(['selling_price' => round($newSellingPrice, 2)]);

            return response()->json([
                'status' => 'success',
                'message' => 'Selling price updated successfully',
                'data' => [
                    'production_cost' => $commodity->production_cost,
                    'old_selling_price' => $oldPrice,
                    'new_selling_price' => $commodity->selling_price,
                    'target_margin' => $targetMargin,
                    'actual_margin' => $commodity->profit_margin,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Calculate costs for multiple commodities
     * POST /api/commodities/calculate-bulk-costs
     */
    public function calculateBulkCosts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commodity_ids' => 'required|array',
            'commodity_ids.*' => 'exists:commodities,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $results = $this->costCalculator->calculateBulkCosts($request->commodity_ids);

        return response()->json([
            'status' => 'success',
            'message' => 'Bulk cost calculation completed',
            'data' => $results
        ]);
    }

    /**
     * Get cost summary for all commodities
     * GET /api/commodities/cost-summary
     */
    public function getAllCommoditiesCostSummary()
    {
        try {
            $commodities = Commodity::with('ingredients')->get();

            $summary = $commodities->map(function ($commodity) {
                return [
                    'id' => $commodity->id,
                    'name' => $commodity->name,
                    'raw_materials_cost' => $commodity->raw_materials_cost ?? 0,
                    'production_cost' => $commodity->production_cost ?? 0,
                    'selling_price' => $commodity->selling_price ?? 0,
                    'profit_margin' => round($commodity->profit_margin, 2),
                    'gross_profit' => ($commodity->selling_price ?? 0) - ($commodity->production_cost ?? 0),
                ];
            });

            $totals = [
                'total_production_costs' => $summary->sum('production_cost'),
                'total_selling_prices' => $summary->sum('selling_price'),
                'total_gross_profit' => $summary->sum('gross_profit'),
                'average_margin' => round($summary->avg('profit_margin'), 2),
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'commodities' => $summary,
                    'totals' => $totals
                ],
                'message' => 'Cost summary retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Recalculate all active commodities
     * POST /api/commodities/recalculate-all
     */
    public function recalculateAllActiveCommodities()
    {
        try {
            $commodities = Commodity::active()->get();
            $results = [];

            foreach ($commodities as $commodity) {
                try {
                    $this->costCalculator->calculateFullCostStructure($commodity);
                    $results[] = [
                        'commodity_id' => $commodity->id,
                        'name' => $commodity->name,
                        'status' => 'success',
                        'production_cost' => $commodity->production_cost,
                        'selling_price' => $commodity->selling_price,
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'commodity_id' => $commodity->id,
                        'name' => $commodity->name,
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Recalculation completed for all active commodities',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validate cost structure percentages
     * GET /api/commodities/validate-cost-structure
     */
    public function validateCostStructure()
    {
        $validation = $this->costCalculator->validateCostPercentages();

        return response()->json([
            'status' => 'success',
            'data' => $validation,
            'message' => 'Cost structure validation completed'
        ]);
    }
}