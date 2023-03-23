<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStockHistory extends Model
{
    use HasFactory;

    protected $fillable=[
        'stock_count',
        'previous_stock_count',
        'submitted_by',
        'product_id'
    ];

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
     }
}
