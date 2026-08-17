<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Promotion\Models\Coupon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class RewardRedemption extends Model
{
    protected $fillable = [
        'reward_offer_id',
        'user_id',
        'coupon_id',
        'point_ledger_entry_id',
        'reversal_point_ledger_entry_id',
        'points_spent',
        'status',
        'idempotency_key',
        'fulfillment_type',
        'fulfillment_id',
        'fulfilled_at',
        'reversed_at',
        'metadata',
    ];

    protected $casts = [
        'points_spent' => 'integer',
        'status' => RewardRedemptionStatusEnum::class,
        'fulfilled_at' => 'datetime',
        'reversed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (RewardRedemption $redemption): void {
            $immutable = [
                'reward_offer_id',
                'user_id',
                'point_ledger_entry_id',
                'points_spent',
                'idempotency_key',
            ];

            if ($redemption->isDirty($immutable)) {
                throw new LogicException('Reward redemption identity cannot be changed.');
            }
        });

        static::deleting(function (): never {
            throw new LogicException('Reward redemptions cannot be deleted.');
        });
    }

    public function rewardOffer(): BelongsTo
    {
        return $this->belongsTo(RewardOffer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function pointLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(PointLedgerEntry::class, 'point_ledger_entry_id');
    }

    public function reversalPointLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(PointLedgerEntry::class, 'reversal_point_ledger_entry_id');
    }

    public function fulfillment(): MorphTo
    {
        return $this->morphTo();
    }

    public function isCompleted(): bool
    {
        return $this->status === RewardRedemptionStatusEnum::COMPLETED;
    }

    public function isReversed(): bool
    {
        return $this->status === RewardRedemptionStatusEnum::REVERSED;
    }
}
