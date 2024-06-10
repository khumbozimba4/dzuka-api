<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class Center extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function centers(): HasMany
    {
        return $this->hasMany(Center::class);
    }

    public static function useFilter(): QueryBuilder
    {
        return QueryBuilder::for(Center::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])->defaultSort('name')
            ->with(['categories'])
            ->allowedSorts('name', 'created_at');
    }

    public function loadRelations(): Center
    {
        return static::load(['categories.products']);
    }
}
