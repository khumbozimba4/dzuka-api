<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laratrust\Models\LaratrustPermission;

class Permission extends LaratrustPermission
{
    public $guarded = [];

    public static function add(string $code, string $endpoint, string $method, string $name, string $group = null): self
    {
        return self::create(['code' => $code, 'endpoint' => $endpoint, 'method' => $method, 'name' => $name, 'group' => $group]);
    }

    public function roles():BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
