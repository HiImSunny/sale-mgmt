<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'code',
        'payment_method',
        'payment_status',
        'status',
        'subtotal',
        'discount_total',
        'grand_total',
        'paid_at',
        'notes',
        'type',
        'parent_order_id',
        'refund_reason',
        'refund_reason_detail'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->code)) {
                $order->code = 'ORD' . date('Ymd') . sprintf('%04d', rand(1, 9999));
            }
        });
        static::updated(function ($order) {
            if ($order->wasChanged('status') && $order->status === 'completed' && $order->customer_id) {
                $order->customer->updateTotalSpent();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function parentOrder()
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function refundOrders()
    {
        return $this->hasMany(Order::class, 'parent_order_id');
    }

// Thêm scopes
    public function scopeSales($query)
    {
        return $query->where('type', 'sale');
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', 'refund');
    }

// Helper methods
    public function isRefund()
    {
        return $this->type === 'refund';
    }

    public function canBeRefunded()
    {
        return $this->type === 'sale' &&
            $this->status === 'confirmed' &&
            $this->payment_status === 'paid';
    }

    public function getTotalRefundedAmount()
    {
        return $this->refundOrders()->sum('grand_total');
    }

    public function getRemainingRefundableAmount()
    {
        return $this->grand_total - $this->getTotalRefundedAmount();
    }
}
