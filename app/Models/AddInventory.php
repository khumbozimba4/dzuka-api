<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddInventory extends Model
{
    use HasFactory;

    protected $guarded;

    public static function getByToday()
    {
        return static::where('created_at', 'like', sprintf('%s%s', now()->toDateString(),"%"))->get();
    }

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
