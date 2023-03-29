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
        'product_photo_path',
        'stock',
    ];


    public function sales():HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function histories():HasMany
    {
        return $this->hasMany(ProductStockHistory::class);
     }

     public function addInventories():HasMany
    {
        return $this->hasMany(AddInventory::class);
    }

    public function submitAuditStocks():HasMany
    {
        return $this->hasMany(SubmitAuditStock::class);
    }

}
