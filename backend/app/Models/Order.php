<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'guest_email',
        'guest_name',
        'guest_phone',
        'status',
        'payment_method',
        'payment_status',
        'payment_id',
        'payment_data',
        'subtotal',
        'delivery_fee',
        'discount',
        'tax',
        'total',
        'delivery_address',
        'notes',
        'estimated_delivery',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_address' => 'array',
        'payment_data' => 'array',
        'estimated_delivery' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_REFUNDED = 'refunded';

    const PAYMENT_STRIPE = 'stripe';
    const PAYMENT_PAYPAL = 'paypal';
    const PAYMENT_COD = 'cod';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isGuest()
    {
        return is_null($this->user_id);
    }

    public function getCustomerNameAttribute()
    {
        return $this->user ? $this->user->name : $this->guest_name;
    }

    public function getCustomerEmailAttribute()
    {
        return $this->user ? $this->user->email : $this->guest_email;
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $order->order_number = 'ORD-' . strtoupper(uniqid());
        });
    }
}
