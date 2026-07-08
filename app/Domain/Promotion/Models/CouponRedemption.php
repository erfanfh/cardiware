<?php

namespace App\Domain\Promotion\Models;

use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponRedemption extends Model
{
    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'code',
        'order_subtotal',
        'eligible_amount',
        'discount_amount',
        'redeemed_at',
        'metadata',
    ];

    protected $casts = [
        'order_subtotal' => 'integer',
        'eligible_amount' => 'integer',
        'discount_amount' => 'integer',
        'redeemed_at' => 'datetime',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
