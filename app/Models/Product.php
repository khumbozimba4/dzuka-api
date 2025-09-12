<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded;

    protected $fillable = [
        'product_name',
        'category_id',
        'SponsorId',
        'price',
        'stock',
        'description',
        'product_photo_path',
        'product_photo_url',
        'supplier_id',
        'brochure_pdf_path'
    ];

    public static function findByZeroStock()
    {
        return static::where('stock', 0)->get();
    }


    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function addInventories(): HasMany
    {
        return $this->hasMany(AddInventory::class);
    }

    public function submitAuditStocks(): HasMany
    {
        return $this->hasMany(SubmitAuditStock::class);
    }

}
