<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable =[
        'product_name',
        'price',
        'description',
        'category_id',
        'sale_id',
        'product_id',
        'quantity',
        'previous_stock',
        'stock',
        'recently_allocated',
        'recently_subtracted'
    ];

    public function sales():BelongsToMany
    {
        return $this->belongsToMany(Sale::class)->withPivot("quantity");
    }

    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function histories():HasMany
    {
        return $this->hasMany(ProductStockHistory::class);
     }

}
