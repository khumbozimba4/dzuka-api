<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommodityIngredient extends Model
{
    use HasFactory;
    protected $fillable = [
        'commodity_id',
        'ingredient_id',
        'quantity_required',
        'cost_per_unit'
    ];

    protected $casts = [
        'quantity_required' => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
    ];

    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function getTotalCostAttribute()
    {
        return $this->quantity_required * $this->cost_per_unit;
    }
}
