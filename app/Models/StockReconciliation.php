<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReconciliation extends Model
{
    use HasFactory;
    protected $fillable = [
        'ingredient_id',
        'reconciled_by',
        'reconciliation_date',
        'system_stock',
        'physical_stock',
        'variance',
        'cost_per_unit',
        'variance_value',
        'notes'
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'system_stock' => 'decimal:3',
        'physical_stock' => 'decimal:3',
        'variance' => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
        'variance_value' => 'decimal:2',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function calculateVariance()
    {
        $this->variance = $this->physical_stock - $this->system_stock;
        $this->variance_value = $this->variance * $this->cost_per_unit;
        $this->save();

        // Update ingredient stock to match physical count
        $this->ingredient->current_stock = $this->physical_stock;
        $this->ingredient->save();

        return $this->variance;
    }

    public function scopeWithVariance($query)
    {
        return $query->where('variance', '!=', 0);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('reconciliation_date', $date);
    }
}
