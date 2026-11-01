<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliateOrderSourceEnum;
use App\Domain\Affiliate\Enums\AffiliateOrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateOrder extends Model
{
    protected $fillable = [
        'affiliate_id',
        'order_id',
        'user_id',
        'order_amount',
        'commission_amount',
        'status',
        'source',
    ];

    protected $casts = [
        'order_amount' => 'integer',
        'commission_amount' => 'integer',
        'status' => AffiliateOrderStatusEnum::class,
        'source' => AffiliateOrderSourceEnum::class,
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
