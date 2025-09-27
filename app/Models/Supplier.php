<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
        'is_active',
        'location',
        'pin',
        'category_id', // <-- add this!
        'center_id'
    ];

    protected $table='suppliers';
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function artisanReports()
    {
        return $this->hasMany(ArtisanReport::class);
    }

    public function ingredientUsage()
    {
        return $this->hasMany(IngredientUsage::class);
    }

    public function completedOrders()
    {
        return $this->hasMany(Order::class)->where('status', 'delivered');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalCommissionAttribute()
    {
        return $this->orders()->sum('artisan_commission');
    }
}
