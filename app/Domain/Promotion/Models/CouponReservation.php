<?php

namespace App\Domain\Promotion\Models;

use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Enums\CouponReservationStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponReservation extends Model
{
    protected $fillable = [
        'coupon_id',
        'user_id',
        'cart_id',
        'order_id',
        'code',
        'eligible_amount',
        'discount_amount',
        'status',
        'reserved_at',
        'expires_at',
        'redeemed_at',
        'released_at',
        'expired_at',
        'metadata',
    ];

    protected $casts = [
        'eligible_amount' => 'integer',
        'discount_amount' => 'integer',
        'status' => CouponReservationStatusEnum::class,
        'reserved_at' => 'datetime',
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'released_at' => 'datetime',
        'expired_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', CouponReservationStatusEnum::ACTIVE)
            ->where('expires_at', '>', now());
    }
}
