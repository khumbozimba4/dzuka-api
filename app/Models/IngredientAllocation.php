<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientAllocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'ingredient_id',
        'allocated_by',
        'quantity_allocated',
        'cost_per_unit',
        'total_cost',
        'allocated_at'
    ];

    protected $casts = [
        'quantity_allocated' => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'allocated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function allocatedBy()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function calculateTotalCost()
    {
        $this->total_cost = $this->quantity_allocated * $this->cost_per_unit;
        $this->save();
    }

}
