<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order',
        'max_uses', 'times_used', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value'      => 'decimal:2',
        'min_order'  => 'decimal:2',
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses && $this->times_used >= $this->max_uses) return false;
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isValid()) return 0;
        if ($subtotal < $this->min_order) return 0;

        return match ($this->type) {
            'percent'       => round($subtotal * ($this->value / 100), 2),
            'flat'          => min($this->value, $subtotal),
            'free_delivery' => 0, // handled separately
            default         => 0,
        };
    }
}
