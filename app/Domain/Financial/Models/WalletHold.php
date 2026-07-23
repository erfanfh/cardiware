<?php

namespace App\Domain\Financial\Models;

use App\Domain\Financial\Enums\WalletDebitPolicyEnum;
use App\Domain\Financial\Enums\WalletHoldStatusEnum;
use App\Domain\Financial\Enums\WalletHoldTypeEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use LogicException;

class WalletHold extends Model
{
    protected $fillable = [
        'wallet_id',
        'actor_user_id',
        'type',
        'status',
        'debit_policy',
        'amount',
        'withdrawable_amount',
        'reference_type',
        'reference_id',
        'idempotency_key',
        'idempotency_fingerprint',
        'description',
        'metadata',
        'expires_at',
    ];

    protected $casts = [
        'type' => WalletHoldTypeEnum::class,
        'status' => WalletHoldStatusEnum::class,
        'debit_policy' => WalletDebitPolicyEnum::class,

        'amount' => 'integer',
        'withdrawable_amount' => 'integer',

        'metadata' => 'array',

        'expires_at' => 'datetime',
        'captured_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (WalletHold $hold): void {
            if (blank($hold->public_token)) {
                $hold->public_token = (string)Str::ulid();
            }

            if ((int)$hold->amount <= 0) {
                throw new LogicException(
                    'Wallet hold amount must be greater than zero.'
                );
            }

            if ((int)$hold->withdrawable_amount < 0) {
                throw new LogicException(
                    'Wallet hold withdrawable amount cannot be negative.'
                );
            }

            if (
                (int)$hold->withdrawable_amount
                > (int)$hold->amount
            ) {
                throw new LogicException(
                    'Wallet hold withdrawable amount cannot exceed total amount.'
                );
            }

            if (
                $hold->type->debitPolicy()
                !== $hold->debit_policy
            ) {
                throw new LogicException(
                    'Wallet hold debit policy does not match its type.'
                );
            }
        });

        static::updating(function (WalletHold $hold): void {
            /*
             * Financial intent fields are immutable after creation.
             * Only resolution-related fields may change.
             */
            $immutableFields = [
                'wallet_id',
                'actor_user_id',
                'type',
                'debit_policy',
                'amount',
                'withdrawable_amount',
                'reference_type',
                'reference_id',
                'idempotency_key',
                'idempotency_fingerprint',
                'public_token',
            ];

            if ($hold->isDirty($immutableFields)) {
                throw new LogicException(
                    'Wallet hold financial fields cannot be updated.'
                );
            }
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Wallet holds cannot be deleted.'
            );
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_token';
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actor_user_id',
        );
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function capturedTransaction(): BelongsTo
    {
        return $this->belongsTo(
            WalletTransaction::class,
            'captured_transaction_id',
        );
    }

    public function capturedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'captured_by_user_id',
        );
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'released_by_user_id',
        );
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isCaptured(): bool
    {
        return $this->status->isCaptured();
    }

    public function isReleased(): bool
    {
        return $this->status->isReleased();
    }

    public function isExpired(): bool
    {
        return $this->status->isExpired();
    }
}
