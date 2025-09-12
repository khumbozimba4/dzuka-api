<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'customer_id',
        'payment_reference',
        'payment_type',
        'amount',
        'payment_method',
        'status',
        'payment_details',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'json',
        'paid_at' => 'datetime',
    ];

    const TYPE_DEPOSIT = 'deposit';
    const TYPE_BALANCE = 'balance';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    const METHOD_CASH = 'cash';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_MOBILE_MONEY = 'mobile_money';
    const METHOD_CARD = 'card';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    public static function generatePaymentReference()
    {
        return 'PAY-' . date('YmdHis') . '-' . rand(1000, 9999);
    }

    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->paid_at = now();
        $this->save();
    }
}
