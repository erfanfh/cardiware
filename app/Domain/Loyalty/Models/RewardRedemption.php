<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Models\Coupon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RewardRedemption extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'reward_offer_id',
        'user_id',
        'point_ledger_entry_id',
        'coupon_id',
        'points_spent',
        'idempotency_key',
        'metadata',
    ];

    protected $casts = [
        'points_spent' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (RewardRedemption $redemption): void {
            if (blank($redemption->public_token)) {
                $redemption->public_token = (string)Str::ulid();
            }
        });
    }

    public function rewardOffer(): BelongsTo
    {
        return $this->belongsTo(RewardOffer::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pointLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(PointLedgerEntry::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class)->withTrashed();
    }
}
