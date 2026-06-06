<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'cart_id',
        'order_number',
        'status',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'paid_at',
        'cancelled_at',
        'metadata',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'subtotal_amount' => 'integer',
        'discount_amount' => 'integer',
        'total_amount' => 'integer',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart that created this order.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)
            ->orderBy('id');
    }

    /**
     * Get root order items.
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(OrderItem::class)
            ->whereNull('parent_order_item_id')
            ->orderBy('id');
    }

    /**
     * Scope paid orders.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::PAID);
    }

    /**
     * Determine whether the order is paid.
     */
    public function isPaid(): bool
    {
        return $this->status->isPaid();
    }
}
