<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $guarded;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class,'center_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
    public function suppliers(): HasMany
    {
    return $this->hasMany(Supplier::class);
    }
}
