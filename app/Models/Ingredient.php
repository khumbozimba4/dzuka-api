<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'unit_of_measurement',
        'cost_per_unit',
        'current_stock',
        'minimum_stock_level',
        'is_active'
    ];

    protected $casts = [
        'cost_per_unit' => 'decimal:2',
        'current_stock' => 'decimal:3',
        'minimum_stock_level' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Commodity::class, 'commodity_ingredients')
                    ->withPivot('quantity_required', 'cost_per_unit')
                    ->withTimestamps();
    }

    public function allocations()
    {
        return $this->hasMany(IngredientAllocation::class);
    }

    public function usage()
    {
        return $this->hasMany(IngredientUsage::class);
    }

    public function reconciliations()
    {
        return $this->hasMany(StockReconciliation::class);
    }

    /**
     * Get warehouses where this ingredient is stored
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_ingredients')
                    ->withPivot('quantity', 'minimum_stock_level', 'maximum_stock_level', 'location_in_warehouse')
                    ->withTimestamps();
    }

    /**
     * Get transfers involving this ingredient
     */
    public function transfers()
    {
        return $this->hasMany(WarehouseTransfer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock_level');
    }

    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock_level;
    }

    /**
     * Check if ingredient is low stock in any warehouse
     */
    public function isLowStockInAnyWarehouse()
    {
        return $this->warehouses()
                    ->wherePivotColumn('quantity', '<=', 'warehouse_ingredients.minimum_stock_level')
                    ->exists();
    }

    /**
     * Get total stock across all warehouses
     */
    public function getTotalStockAcrossWarehousesAttribute()
    {
        return $this->warehouses->sum('pivot.quantity');
    }

    /**
     * Get stock in specific warehouse
     */
    public function getStockInWarehouse($warehouseId)
    {
        $warehouse = $this->warehouses()->where('warehouse_id', $warehouseId)->first();
        return $warehouse ? $warehouse->pivot->quantity : 0;
    }

    public function reduceStock($quantity)
    {
        $this->current_stock -= $quantity;
        $this->save();
    }

    public function addStock($quantity)
    {
        $this->current_stock += $quantity;
        $this->save();
    }

    /**
     * Reduce stock from specific warehouse
     */
    public function reduceStockFromWarehouse($warehouseId, $quantity)
    {
        $warehouse = $this->warehouses()->where('warehouse_id', $warehouseId)->first();

        if (!$warehouse) {
            throw new \Exception('Ingredient not found in this warehouse');
        }

        $currentQuantity = $warehouse->pivot->quantity;

        if ($currentQuantity < $quantity) {
            throw new \Exception('Insufficient stock in warehouse');
        }

        $this->warehouses()->updateExistingPivot($warehouseId, [
            'quantity' => $currentQuantity - $quantity
        ]);

        // Also update total stock
        $this->reduceStock($quantity);
    }

    /**
     * Add stock to specific warehouse
     */
    public function addStockToWarehouse($warehouseId, $quantity)
    {
        $warehouse = $this->warehouses()->where('warehouse_id', $warehouseId)->first();

        if (!$warehouse) {
            // Create new warehouse ingredient entry
            $this->warehouses()->attach($warehouseId, [
                'quantity' => $quantity,
                'minimum_stock_level' => $this->minimum_stock_level
            ]);
        } else {
            // Update existing
            $currentQuantity = $warehouse->pivot->quantity;
            $this->warehouses()->updateExistingPivot($warehouseId, [
                'quantity' => $currentQuantity + $quantity
            ]);
        }

        // Also update total stock
        $this->addStock($quantity);
    }
}