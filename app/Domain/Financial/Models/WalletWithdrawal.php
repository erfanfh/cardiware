<?php

namespace App\Domain\Financial\Models;

use App\Domain\Financial\Enums\WalletWithdrawalStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WalletWithdrawal extends Model
{
    protected $fillable = [
        'user_id', 'wallet_id', 'idempotency_key', 'status', 'amount',
        'iban', 'card_number', 'account_holder', 'user_note',
    ];

    protected $casts = [
        'status' => WalletWithdrawalStatusEnum::class,
        'amount' => 'integer',
        'iban' => 'encrypted',
        'card_number' => 'encrypted',
        'account_holder' => 'encrypted',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereIn('status', [
            WalletWithdrawalStatusEnum::PENDING,
            WalletWithdrawalStatusEnum::APPROVED,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function hold(): BelongsTo
    {
        return $this->belongsTo(WalletHold::class, 'hold_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    public function maskedIban(): string
    {
        return substr($this->iban, 0, 6) . str_repeat('*', 16) . substr($this->iban, -4);
    }

    public function maskedCardNumber(): ?string
    {
        return $this->card_number === null ? null : '**** **** **** ' . substr($this->card_number, -4);
    }
}
