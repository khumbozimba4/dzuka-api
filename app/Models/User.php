<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function audits(): HasMany
    {
        return $this->hasMany(SubmitAuditStock::class);
    }

    public function role():BelongsTo
    {
        return $this->belongsTo(Role::class,'role_id');
    }

    public function center():BelongsTo
    {
        return $this->belongsTo(Center::class,'center_id');
    }

    public function assignedOrders()
    {
        return $this->hasMany(Order::class, 'assigned_by');
    }

    const ROLES = [
        'admin' => 'Admin',
        'inventory_manager' => 'Inventory Manager',
        'order_manager' => 'Order Manager',
        'delivery_manager' => 'Delivery Manager'
    ];


    public function ingredientAllocations()
    {
        return $this->hasMany(IngredientAllocation::class, 'allocated_by');
    }

    public function stockReconciliations()
    {
        return $this->hasMany(StockReconciliation::class, 'reconciled_by');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'assigned_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getRoleNameAttribute()
    {
        return self::ROLES[$this->role] ?? $this->role;
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }


}
