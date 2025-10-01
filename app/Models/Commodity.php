<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    use HasFactory;

    protected $fillable = [
        'sector_id',
        'name',
        'description',
        'image',
        'selling_price',
        'production_cost',
        'production_time_hours',
        'is_active',
        // Direct Costs
        'raw_materials_cost',
        'direct_labor_cost',
        // Indirect Costs
        'utilities_cost',
        'machinery_maintenance_cost',
        'facility_rent_cost',
        'packaging_cost',
        // Administrative and Sales
        'admin_support_cost',
        'machinery_depreciation_cost',
        'marketing_cost',
        'rd_cost',
        'market_testing_cost',
        'quality_assurance_cost',
        // Essential Additional
        'sustainability_cost',
        'staff_training_cost',
        'insurance_cost',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'production_cost' => 'decimal:2',
        'production_time_hours' => 'integer',
        'is_active' => 'boolean',
        'raw_materials_cost' => 'decimal:2',
        'direct_labor_cost' => 'decimal:2',
        'utilities_cost' => 'decimal:2',
        'machinery_maintenance_cost' => 'decimal:2',
        'facility_rent_cost' => 'decimal:2',
        'packaging_cost' => 'decimal:2',
        'admin_support_cost' => 'decimal:2',
        'machinery_depreciation_cost' => 'decimal:2',
        'marketing_cost' => 'decimal:2',
        'rd_cost' => 'decimal:2',
        'market_testing_cost' => 'decimal:2',
        'quality_assurance_cost' => 'decimal:2',
        'sustainability_cost' => 'decimal:2',
        'staff_training_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
    ];

    // Cost percentages as constants
    const COST_PERCENTAGES = [
        'raw_materials' => 40,
        'direct_labor' => 25,
        'utilities' => 5,
        'machinery_maintenance' => 5,
        'facility_rent' => 7,
        'packaging' => 3,
        'admin_support' => 4,
        'machinery_depreciation' => 2,
        'marketing' => 2,
        'rd' => 2,
        'market_testing' => 1,
        'quality_assurance' => 2,
        'sustainability' => 1,
        'staff_training' => 1,
        'insurance' => 1,
    ];

    const MARKUP_PERCENTAGE = 20;
    const DZUKA_DONATION = 25;
    const STAFF_COMMISSION = 5;
    const TAXATION = 30;
    const PAMODZA_PROFIT = 15;
    const SIX_TO_SIX_PROFIT = 25;

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'commodity_ingredients')
                    ->withPivot('quantity_required', 'cost_per_unit')
                    ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySector($query, $sectorId)
    {
        return $query->where('sector_id', $sectorId);
    }

    /**
     * Calculate raw materials cost from ingredients
     */
    public function calculateRawMaterialsCost()
    {
        return $this->ingredients->sum(function ($ingredient) {
            return $ingredient->pivot->quantity_required * $ingredient->pivot->cost_per_unit;
        });
    }

    /**
     * Calculate total direct costs (raw materials + direct labor)
     */
    public function calculateDirectCosts()
    {
        $rawMaterials = $this->raw_materials_cost ?? $this->calculateRawMaterialsCost();
        $directLabor = $this->direct_labor_cost ?? 0;

        return $rawMaterials + $directLabor;
    }

    /**
     * Calculate total indirect costs
     */
    public function calculateIndirectCosts()
    {
        return ($this->utilities_cost ?? 0) +
               ($this->machinery_maintenance_cost ?? 0) +
               ($this->facility_rent_cost ?? 0) +
               ($this->packaging_cost ?? 0);
    }

    /**
     * Calculate administrative and sales expenses
     */
    public function calculateAdminAndSalesCosts()
    {
        return ($this->admin_support_cost ?? 0) +
               ($this->machinery_depreciation_cost ?? 0) +
               ($this->marketing_cost ?? 0) +
               ($this->rd_cost ?? 0) +
               ($this->market_testing_cost ?? 0) +
               ($this->quality_assurance_cost ?? 0);
    }

    /**
     * Calculate essential additional expenses
     */
    public function calculateEssentialCosts()
    {
        return ($this->sustainability_cost ?? 0) +
               ($this->staff_training_cost ?? 0) +
               ($this->insurance_cost ?? 0);
    }

    /**
     * Calculate total production cost
     */
    public function calculateProductionCost()
    {
        $directCosts = $this->calculateDirectCosts();
        $indirectCosts = $this->calculateIndirectCosts();
        $adminCosts = $this->calculateAdminAndSalesCosts();
        $essentialCosts = $this->calculateEssentialCosts();

        $totalCost = $directCosts + $indirectCosts + $adminCosts + $essentialCosts;

        $this->production_cost = $totalCost;
        $this->save();

        return $totalCost;
    }

    /**
     * Calculate all costs based on raw materials using percentages
     */
    public function calculateAllCostsFromRawMaterials()
    {
        // Get raw materials cost from ingredients
        $rawMaterialsCost = $this->calculateRawMaterialsCost();

        // Raw materials represents 40% of total cost
        // So total cost = raw materials / 0.40
        $totalProductionCost = $rawMaterialsCost / (self::COST_PERCENTAGES['raw_materials'] / 100);

        // Calculate each cost component
        $this->raw_materials_cost = $rawMaterialsCost;
        $this->direct_labor_cost = $totalProductionCost * (self::COST_PERCENTAGES['direct_labor'] / 100);
        $this->utilities_cost = $totalProductionCost * (self::COST_PERCENTAGES['utilities'] / 100);
        $this->machinery_maintenance_cost = $totalProductionCost * (self::COST_PERCENTAGES['machinery_maintenance'] / 100);
        $this->facility_rent_cost = $totalProductionCost * (self::COST_PERCENTAGES['facility_rent'] / 100);
        $this->packaging_cost = $totalProductionCost * (self::COST_PERCENTAGES['packaging'] / 100);
        $this->admin_support_cost = $totalProductionCost * (self::COST_PERCENTAGES['admin_support'] / 100);
        $this->machinery_depreciation_cost = $totalProductionCost * (self::COST_PERCENTAGES['machinery_depreciation'] / 100);
        $this->marketing_cost = $totalProductionCost * (self::COST_PERCENTAGES['marketing'] / 100);
        $this->rd_cost = $totalProductionCost * (self::COST_PERCENTAGES['rd'] / 100);
        $this->market_testing_cost = $totalProductionCost * (self::COST_PERCENTAGES['market_testing'] / 100);
        $this->quality_assurance_cost = $totalProductionCost * (self::COST_PERCENTAGES['quality_assurance'] / 100);
        $this->sustainability_cost = $totalProductionCost * (self::COST_PERCENTAGES['sustainability'] / 100);
        $this->staff_training_cost = $totalProductionCost * (self::COST_PERCENTAGES['staff_training'] / 100);
        $this->insurance_cost = $totalProductionCost * (self::COST_PERCENTAGES['insurance'] / 100);

        $this->production_cost = $totalProductionCost;
        $this->save();

        return $totalProductionCost;
    }

    /**
     * Calculate selling price with markup
     */
    public function calculateSellingPrice()
    {
        $productionCost = $this->production_cost ?? $this->calculateProductionCost();
        $markup = $productionCost * (self::MARKUP_PERCENTAGE / 100);

        return $productionCost + $markup;
    }

    /**
     * Get detailed cost breakdown
     */
    public function getCostBreakdown()
    {
        return [
            'direct_costs' => [
                'raw_materials' => [
                    'amount' => $this->raw_materials_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['raw_materials'],
                ],
                'direct_labor' => [
                    'amount' => $this->direct_labor_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['direct_labor'],
                ],
                'subtotal' => $this->calculateDirectCosts(),
            ],
            'indirect_costs' => [
                'utilities' => [
                    'amount' => $this->utilities_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['utilities'],
                ],
                'machinery_maintenance' => [
                    'amount' => $this->machinery_maintenance_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['machinery_maintenance'],
                ],
                'facility_rent' => [
                    'amount' => $this->facility_rent_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['facility_rent'],
                ],
                'packaging' => [
                    'amount' => $this->packaging_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['packaging'],
                ],
                'subtotal' => $this->calculateIndirectCosts(),
            ],
            'admin_and_sales' => [
                'admin_support' => [
                    'amount' => $this->admin_support_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['admin_support'],
                ],
                'machinery_depreciation' => [
                    'amount' => $this->machinery_depreciation_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['machinery_depreciation'],
                ],
                'marketing' => [
                    'amount' => $this->marketing_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['marketing'],
                ],
                'rd' => [
                    'amount' => $this->rd_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['rd'],
                ],
                'market_testing' => [
                    'amount' => $this->market_testing_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['market_testing'],
                ],
                'quality_assurance' => [
                    'amount' => $this->quality_assurance_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['quality_assurance'],
                ],
                'subtotal' => $this->calculateAdminAndSalesCosts(),
            ],
            'essential_costs' => [
                'sustainability' => [
                    'amount' => $this->sustainability_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['sustainability'],
                ],
                'staff_training' => [
                    'amount' => $this->staff_training_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['staff_training'],
                ],
                'insurance' => [
                    'amount' => $this->insurance_cost ?? 0,
                    'percentage' => self::COST_PERCENTAGES['insurance'],
                ],
                'subtotal' => $this->calculateEssentialCosts(),
            ],
            'total_production_cost' => $this->production_cost,
            'markup' => $this->production_cost * (self::MARKUP_PERCENTAGE / 100),
            'selling_price' => $this->calculateSellingPrice(),
        ];
    }

    /**
     * Get revenue distribution breakdown
     */
    public function getRevenueDistribution()
    {
        $sellingPrice = $this->selling_price ?? $this->calculateSellingPrice();
        $productionCost = $this->production_cost;
        $profitBeforeTaxAndCommission = $sellingPrice - $productionCost;

        return [
            'total_revenue' => $sellingPrice,
            'production_cost' => $productionCost,
            'profit_before_tax_commission' => $profitBeforeTaxAndCommission,
            'distributions' => [
                'dzuka_donation' => $profitBeforeTaxAndCommission * (self::DZUKA_DONATION / 100),
                'staff_commission' => $profitBeforeTaxAndCommission * (self::STAFF_COMMISSION / 100),
                'taxation' => $profitBeforeTaxAndCommission * (self::TAXATION / 100),
                'pamodza_profit' => $profitBeforeTaxAndCommission * (self::PAMODZA_PROFIT / 100),
                'six_to_six_profit' => $profitBeforeTaxAndCommission * (self::SIX_TO_SIX_PROFIT / 100),
            ],
        ];
    }

    public function getProfitMarginAttribute()
    {
        if ($this->production_cost > 0 && $this->selling_price > 0) {
            return (($this->selling_price - $this->production_cost) / $this->selling_price) * 100;
        }
        return 0;
    }
}