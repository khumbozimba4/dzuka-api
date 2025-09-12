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
        return $this->belongsToMany(Commodity::class, 'product_ingredients')
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
}
