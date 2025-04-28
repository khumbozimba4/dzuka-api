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
        'location',
        'pin',
        'category_id', // <-- add this!
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
