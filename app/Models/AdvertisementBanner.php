<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertisementBanner extends Model
{
    protected $table = 'AdvertisementBanners';
    protected $primaryKey = 'BannerId';
    public $timestamps = false; // Because you manually manage CreatedAt and UpdatedAt

    protected $fillable = [
        'Title',
        'BannerImageUrl',
        'LinkUrl',
        'Description',
        'IsActive',
        'StartDate',
        'EndDate',
    ];
}
