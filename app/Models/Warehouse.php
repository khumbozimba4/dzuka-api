<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'location',
        'address',
        'contact_person',
        'contact_phone',
        'contact_email',
        'type',
        'storage_capacity',
        'is_active'
    ];

    protected $casts = [
        'storage_capacity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get ingredients in this warehouse with their stock levels
     */
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'warehouse_ingredients')
                    ->withPivot('quantity', 'minimum_stock_level', 'maximum_stock_level', 'location_in_warehouse')
                    ->withTimestamps();
    }

    /**
     * Get outgoing transfers from this warehouse
     */
    public function outgoingTransfers()
    {
        return $this->hasMany(WarehouseTransfer::class, 'from_warehouse_id');
    }

    /**
     * Get incoming transfers to this warehouse
     */
    public function incomingTransfers()
    {
        return $this->hasMany(WarehouseTransfer::class, 'to_warehouse_id');
    }

    /**
     * Scope to get only active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get low stock items in this warehouse
     */
    public function lowStockIngredients()
    {
        return $this->ingredients()
                    ->wherePivot('quantity', '<=', 'warehouse_ingredients.minimum_stock_level');
    }

    /**
     * Get total stock value in this warehouse
     */
    public function getTotalStockValueAttribute()
    {
        return $this->ingredients->sum(function ($ingredient) {
            return $ingredient->pivot->quantity * $ingredient->cost_per_unit;
        });
    }

    /**
     * Get current utilization percentage
     */
    public function getUtilizationPercentageAttribute()
    {
        if (!$this->storage_capacity) {
            return 0;
        }

        $totalVolume = $this->ingredients->sum('pivot.quantity');
        return ($totalVolume / $this->storage_capacity) * 100;
    }

    /**
     * Check if warehouse has sufficient stock of an ingredient
     */
    public function hasSufficientStock($ingredientId, $quantity)
    {
        $ingredient = $this->ingredients()->where('ingredient_id', $ingredientId)->first();

        if (!$ingredient) {
            return false;
        }

        return $ingredient->pivot->quantity >= $quantity;
    }
}