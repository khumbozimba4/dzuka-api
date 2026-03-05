<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'ingredient_id',
        'quantity',
        'status',
        'initiated_by',
        'approved_by',
        'received_by',
        'notes',
        'initiated_at',
        'approved_at',
        'completed_at'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'initiated_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transfer) {
            if (!$transfer->transfer_number) {
                $transfer->transfer_number = 'TRF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }

    /**
     * Get the source warehouse
     */
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Get the destination warehouse
     */
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Get the ingredient being transferred
     */
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    /**
     * Get the user who initiated the transfer
     */
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get the user who approved the transfer
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who received the transfer
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Scope for pending transfers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in-transit transfers
     */
    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    /**
     * Scope for completed transfers
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}