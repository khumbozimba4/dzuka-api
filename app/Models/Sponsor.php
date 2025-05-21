<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sponsor extends Model
{
    use HasFactory;

    protected $table = 'Sponsors';
    protected $primaryKey = 'SponsorId';
    public $timestamps = false; 
    protected $fillable = [
        'SponsorName',
        'LogoUrl',
        'Description',
        'ContactInfo',
        'SponsorSince',
        'IsActive',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
