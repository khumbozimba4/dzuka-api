<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'assigned_by',
        'delivery_method',
        'delivery_address',
        'delivery_fee',
        'status',
        'scheduled_at',
        'delivered_at',
        'delivery_notes',
        'delivered_by'
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';

    const METHOD_PICKUP = 'pickup';
    const METHOD_COURIER = 'courier';
    const METHOD_DZUKA_DELIVERY = 'dzuka_delivery';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeScheduledForToday($query)
    {
        return $query->whereDate('scheduled_at', today())
            ->where('status', self::STATUS_SCHEDULED);
    }

    public function markAsDelivered($deliveredBy = null, $notes = null)
    {
        $this->status = self::STATUS_DELIVERED;
        $this->delivered_at = now();
        $this->delivered_by = $deliveredBy;
        $this->delivery_notes = $notes;
        $this->save();

        // Update order status
        $this->order->status = Order::STATUS_DELIVERED;
        $this->order->delivered_at = now();
        $this->order->save();
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }
}
