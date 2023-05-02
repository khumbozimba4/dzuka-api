<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;

    protected $guarded;

    public static function findByToday()
    {
        return static::whereDate('created_at', Carbon::today())->get();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
