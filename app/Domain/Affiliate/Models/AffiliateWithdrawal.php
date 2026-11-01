<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AffiliateWithdrawal extends Model
{
    protected $fillable = [
        'affiliate_id',
        'amount',
        'status',
        'destination',
        'user_note',
        'admin_note',
        'requested_at',
        'resolved_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'status' => AffiliateWithdrawalStatusEnum::class,
        'requested_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $withdrawal): void {
            $withdrawal->public_token ??= (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_token';
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }
}
