<?php

namespace App\Domain\Financial\Models;

use App\Domain\Financial\Enums\WalletTransactionDirectionEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use LogicException;

class WalletTransaction extends Model
{
    /**
     * Wallet transactions have created_at but do not have updated_at.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'wallet_id',
        'actor_user_id',
        'type',
        'direction',
        'amount',
        'tax_amount',
        'withdrawable_amount',
        'balance_before',
        'balance_after',
        'withdrawable_balance_before',
        'withdrawable_balance_after',
        'reference_type',
        'reference_id',
        'idempotency_key',
        'idempotency_fingerprint',
        'description',
        'metadata',
    ];

    protected $casts = [
        'type' => WalletTransactionTypeEnum::class,
        'direction' => WalletTransactionDirectionEnum::class,

        'amount' => 'integer',
        'tax_amount' => 'integer',
        'withdrawable_amount' => 'integer',

        'balance_before' => 'integer',
        'balance_after' => 'integer',

        'withdrawable_balance_before' => 'integer',
        'withdrawable_balance_after' => 'integer',

        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (WalletTransaction $transaction): void {
            if (blank($transaction->public_token)) {
                $transaction->public_token = (string)Str::ulid();
            }

            if ((int)$transaction->amount <= 0) {
                throw new LogicException(
                    'Wallet transaction amount must be greater than zero.'
                );
            }

            if (
                (int)$transaction->withdrawable_amount
                > (int)$transaction->amount
            ) {
                throw new LogicException(
                    'Withdrawable transaction amount cannot exceed total amount.'
                );
            }

            if (
                $transaction->type->direction()
                !== $transaction->direction
            ) {
                throw new LogicException(
                    'Wallet transaction direction does not match its type.'
                );
            }
        });

        /*
         * Wallet transactions are immutable.
         */
        static::updating(function (): never {
            throw new LogicException(
                'Wallet transactions cannot be updated.'
            );
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Wallet transactions cannot be deleted.'
            );
        });
    }

    /**
     * Get the affected wallet.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the user that initiated the transaction.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actor_user_id',
        );
    }

    /**
     * Get the related domain object.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Determine whether this is a credit transaction.
     */
    public function isCredit(): bool
    {
        return $this->direction->isCredit();
    }

    /**
     * Determine whether this is a debit transaction.
     */
    public function isDebit(): bool
    {
        return $this->direction->isDebit();
    }

    /**
     * Get the signed transaction amount.
     */
    public function signedAmount(): int
    {
        return $this->isCredit()
            ? $this->amount
            : -$this->amount;
    }
}
