<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'birthday', 'gender',
        'address', 'notes', 'total_orders', 'total_spent',
        'customer_tier', 'is_vip'
    ];

    protected $casts = [
        'birthday' => 'date',
        'total_spent' => 'decimal:2',
        'is_vip' => 'boolean',
    ];

    // Relationship với orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function updateTotalSpent(): void
    {
        $this->total_spent = $this->orders()->where('status', 'completed')->sum('grand_total');
        $this->total_orders = $this->orders()->where('status', 'completed')->count();
        $this->updateCustomerTier();
        $this->save();
    }

    public function getFormattedTotalSpentAttribute(): string
    {
        return number_format($this->total_spent) . 'đ';
    }

    public function getTierBadgeClassAttribute(): string
    {
        return match($this->customer_tier) {
            'platinum' => 'primary',
            'gold' => 'warning',
            'silver' => 'info',
            'bronze' => 'secondary',
        };
    }
}
