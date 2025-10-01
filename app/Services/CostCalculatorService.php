<?php

namespace App\Services;

use App\Models\Commodity;

class CostCalculatorService
{
    /**
     * Calculate and update all costs for a commodity based on raw materials
     */
    public function calculateFullCostStructure(Commodity $commodity)
    {
        // Calculate raw materials from ingredients
        $rawMaterialsCost = $commodity->calculateRawMaterialsCost();

        if ($rawMaterialsCost <= 0) {
            throw new \Exception('Raw materials cost must be greater than zero');
        }

        // Raw materials represents 40% of total cost
        $totalProductionCost = $rawMaterialsCost / 0.40;

        // Update all cost components
        $commodity->update([
            'raw_materials_cost' => round($rawMaterialsCost, 2),
            'direct_labor_cost' => round($totalProductionCost * 0.25, 2),
            'utilities_cost' => round($totalProductionCost * 0.05, 2),
            'machinery_maintenance_cost' => round($totalProductionCost * 0.05, 2),
            'facility_rent_cost' => round($totalProductionCost * 0.07, 2),
            'packaging_cost' => round($totalProductionCost * 0.03, 2),
            'admin_support_cost' => round($totalProductionCost * 0.04, 2),
            'machinery_depreciation_cost' => round($totalProductionCost * 0.02, 2),
            'marketing_cost' => round($totalProductionCost * 0.02, 2),
            'rd_cost' => round($totalProductionCost * 0.02, 2),
            'market_testing_cost' => round($totalProductionCost * 0.01, 2),
            'quality_assurance_cost' => round($totalProductionCost * 0.02, 2),
            'sustainability_cost' => round($totalProductionCost * 0.01, 2),
            'staff_training_cost' => round($totalProductionCost * 0.01, 2),
            'insurance_cost' => round($totalProductionCost * 0.01, 2),
            'production_cost' => round($totalProductionCost, 2),
        ]);

        // Calculate and update selling price with 20% markup
        $sellingPrice = round($totalProductionCost * 1.20, 2);
        $commodity->update(['selling_price' => $sellingPrice]);

        return $commodity->fresh();
    }

    /**
     * Get detailed cost report for a commodity
     */
    public function getCostReport(Commodity $commodity)
    {
        $breakdown = $commodity->getCostBreakdown();
        $revenue = $commodity->getRevenueDistribution();

        return [
            'commodity' => [
                'id' => $commodity->id,
                'name' => $commodity->name,
                'production_time_hours' => $commodity->production_time_hours,
            ],
            'cost_breakdown' => $breakdown,
            'revenue_distribution' => $revenue,
            'summary' => [
                'total_production_cost' => $commodity->production_cost,
                'selling_price' => $commodity->selling_price,
                'profit_margin' => round($commodity->profit_margin, 2) . '%',
                'gross_profit' => $commodity->selling_price - $commodity->production_cost,
            ],
        ];
    }

    /**
     * Calculate costs for multiple commodities
     */
    public function calculateBulkCosts($commodityIds)
    {
        $results = [];

        foreach ($commodityIds as $commodityId) {
            $commodity = Commodity::find($commodityId);
            if ($commodity) {
                try {
                    $updated = $this->calculateFullCostStructure($commodity);
                    $results[] = [
                        'commodity_id' => $commodityId,
                        'name' => $commodity->name,
                        'status' => 'success',
                        'production_cost' => $updated->production_cost,
                        'selling_price' => $updated->selling_price,
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'commodity_id' => $commodityId,
                        'name' => $commodity->name,
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Validate cost percentages sum to 100%
     */
    public function validateCostPercentages()
    {
        $total = array_sum(Commodity::COST_PERCENTAGES);

        return [
            'valid' => $total === 100,
            'total' => $total,
            'percentages' => Commodity::COST_PERCENTAGES,
        ];
    }

    /**
     * Get cost comparison for a commodity's ingredients vs calculated costs
     */
    public function getIngredientCostComparison(Commodity $commodity)
    {
        $ingredientsCost = $commodity->calculateRawMaterialsCost();
        $storedRawMaterialsCost = $commodity->raw_materials_cost ?? 0;

        $ingredients = $commodity->ingredients->map(function ($ingredient) {
            return [
                'name' => $ingredient->name,
                'quantity_required' => $ingredient->pivot->quantity_required,
                'cost_per_unit' => $ingredient->pivot->cost_per_unit,
                'total_cost' => $ingredient->pivot->quantity_required * $ingredient->pivot->cost_per_unit,
                'unit' => $ingredient->unit_of_measurement,
            ];
        });

        return [
            'calculated_from_ingredients' => round($ingredientsCost, 2),
            'stored_raw_materials_cost' => round($storedRawMaterialsCost, 2),
            'difference' => round($ingredientsCost - $storedRawMaterialsCost, 2),
            'needs_update' => abs($ingredientsCost - $storedRawMaterialsCost) > 0.01,
            'ingredients_detail' => $ingredients,
        ];
    }

    /**
     * Calculate cost per hour of production
     */
    public function getCostPerHour(Commodity $commodity)
    {
        if ($commodity->production_time_hours <= 0) {
            throw new \Exception('Production time must be greater than zero');
        }

        return [
            'production_time_hours' => $commodity->production_time_hours,
            'total_production_cost' => $commodity->production_cost,
            'cost_per_hour' => round($commodity->production_cost / $commodity->production_time_hours, 2),
            'direct_labor_per_hour' => round(($commodity->direct_labor_cost ?? 0) / $commodity->production_time_hours, 2),
        ];
    }
}