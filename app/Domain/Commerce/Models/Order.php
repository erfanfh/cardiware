<?php

namespace App\Domain\Commerce\Models;

use App\Application\Financial\Contracts\Payable;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\WalletTopUp;
use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Models\Coupon;
use App\Domain\Promotion\Models\CouponReservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Order extends Model implements Payable
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
        'coupon_id',
        'coupon_code',
        'coupon_eligible_amount',
        'coupon_discount_amount',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'subtotal_amount' => 'integer',
        'discount_amount' => 'integer',
        'total_amount' => 'integer',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
        'coupon_eligible_amount' => 'integer',
        'coupon_discount_amount' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

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

    /**
     * Get order payments.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable')
            ->latest('id');
    }

    /**
     * Get latest payment.
     */
    public function latestPayment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable')
            ->latestOfMany();
    }

    /**
     * Get payable amount in toman.
     */
    public function getPayableAmount(): int
    {
        return (int)$this->total_amount;
    }

    /**
     * Get payable display title.
     */
    public function getPayableTitle(): string
    {
        return 'سفارش ' . $this->order_number;
    }

    /**
     * Determine whether this payable can start payment.
     */
    public function canStartPayment(): bool
    {
        return $this->status->canStartPayment();
    }

    /**
     * Get purchase accesses granted by this order.
     */
    public function purchaseAccesses(): HasMany
    {
        return $this->hasMany(PurchaseAccess::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function couponReservation(): HasOne
    {
        return $this->hasOne(CouponReservation::class);
    }

    /**
     * Wallet top-ups created to complete this order payment.
     */
    public function walletTopUps(): HasMany
    {
        return $this->hasMany(WalletTopUp::class);
    }
}
