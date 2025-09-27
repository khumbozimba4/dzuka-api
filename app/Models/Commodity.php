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
        'is_active'
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'production_cost' => 'decimal:2',
        'production_time_hours' => 'integer',
        'is_active' => 'boolean',
    ];

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

    public function calculateProductionCost()
    {
        $cost = $this->ingredients->sum(function ($ingredient) {
            return $ingredient->pivot->quantity_required * $ingredient->pivot->cost_per_unit;
        });

        $this->production_cost = $cost;
        $this->save();

        return $cost;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->production_cost > 0) {
            return (($this->selling_price - $this->production_cost) / $this->selling_price) * 100;
        }
        return 0;
    }
}
