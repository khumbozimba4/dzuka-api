<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'user_id',
        'supplier_id',
        'assigned_by',
        'status',
        'total_amount',
        'deposit_amount',
        'balance_amount',
        'artisan_commission',
        'overhead_amount',
        'customer_notes',
        'admin_notes',
        'artisan_notes',
        'assigned_at',
        'accepted_at',
        'deposit_paid_at',
        'production_started_at',
        'production_completed_at',
        'delivered_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'artisan_commission' => 'decimal:2',
        'overhead_amount' => 'decimal:2',
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'deposit_paid_at' => 'datetime',
        'production_started_at' => 'datetime',
        'production_completed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED_TO_ARTISAN = 'assigned_to_artisan';
    const STATUS_ACCEPTED_BY_ARTISAN = 'accepted_by_artisan';
    const STATUS_DEPOSIT_REQUESTED = 'deposit_requested';
    const STATUS_DEPOSIT_PAID = 'deposit_paid';
    const STATUS_INGREDIENTS_PROVIDED = 'ingredients_provided';
    const STATUS_IN_PRODUCTION = 'in_production';
    const STATUS_PRODUCTION_COMPLETED = 'production_completed';
    const STATUS_BALANCE_REQUESTED = 'balance_requested';
    const STATUS_BALANCE_PAID = 'balance_paid';
    const STATUS_READY_FOR_DELIVERY = 'ready_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    public function customer()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function ingredientAllocations()
    {
        return $this->hasMany(IngredientAllocation::class);
    }

    public function ingredientUsage()
    {
        return $this->hasMany(IngredientUsage::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('user_id', $customerId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function calculateCommissions()
    {
        $this->artisan_commission = $this->total_amount * 0.25; // 25%
        $this->overhead_amount = $this->total_amount * 0.35; // 35%
        $this->balance_amount = $this->total_amount - $this->deposit_amount;
        $this->save();
    }

    public function calculateDepositAmount($percentage = 50)
    {
        $this->deposit_amount = $this->total_amount * ($percentage / 100);
        $this->balance_amount = $this->total_amount - $this->deposit_amount;
        $this->save();
    }

    public static function generateOrderNumber()
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'DZK-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function isDelivered()
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeAssigned()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeAccepted()
    {
        return $this->status === self::STATUS_ASSIGNED_TO_ARTISAN;
    }
}
