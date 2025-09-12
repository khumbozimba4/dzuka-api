<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientUsage extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'ingredient_id',
        'supplier_id',
        'quantity_used',
        'quantity_allocated',
        'variance',
        'cost_per_unit',
        'variance_cost',
        'notes',
        'recorded_at'
    ];

    protected $casts = [
        'quantity_used' => 'decimal:3',
        'quantity_allocated' => 'decimal:3',
        'variance' => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
        'variance_cost' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function calculateVariance()
    {
        $this->variance = $this->quantity_used - $this->quantity_allocated;
        $this->variance_cost = $this->variance * $this->cost_per_unit;
        $this->save();
    }

    public function scopeWithVariance($query)
    {
        return $query->where('variance', '!=', 0);
    }

    public function scopeOverUsage($query)
    {
        return $query->where('variance', '>', 0);
    }

    public function scopeUnderUsage($query)
    {
        return $query->where('variance', '<', 0);
    }
}
