<?php

namespace App\Domain\Financial\Models;

use App\Application\Financial\Contracts\Payable;
use App\Domain\Financial\Enums\WalletTopUpStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use LogicException;

class WalletTopUp extends Model implements Payable
{
    protected $fillable = [
        'wallet_id',
        'request_key',
        'status',
        'amount',
        'withdrawable_amount',
        'metadata',
        'expires_at',
    ];

    protected $casts = [
        'status' => WalletTopUpStatusEnum::class,
        'amount' => 'integer',
        'withdrawable_amount' => 'integer',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (WalletTopUp $topUp): void {
            if (blank($topUp->public_token)) {
                $topUp->public_token = (string)Str::ulid();
            }

            if ((int)$topUp->amount <= 0) {
                throw new LogicException(
                    'Wallet top-up amount must be greater than zero.'
                );
            }

            if ((int)$topUp->withdrawable_amount < 0) {
                throw new LogicException(
                    'Wallet top-up withdrawable amount cannot be negative.'
                );
            }

            if (
                (int)$topUp->withdrawable_amount
                > (int)$topUp->amount
            ) {
                throw new LogicException(
                    'Wallet top-up withdrawable amount cannot exceed total amount.'
                );
            }
        });

        static::updating(function (WalletTopUp $topUp): void {
            $immutableFields = [
                'public_token',
                'request_key',
                'wallet_id',
                'amount',
                'withdrawable_amount',
            ];

            if ($topUp->isDirty($immutableFields)) {
                throw new LogicException(
                    'Wallet top-up financial fields cannot be updated.'
                );
            }

            /*
             * Once assigned, payment and credit transaction cannot
             * be replaced with another record.
             */
            if (
                $topUp->isDirty('payment_id')
                && $topUp->getOriginal('payment_id') !== null
            ) {
                throw new LogicException(
                    'Wallet top-up payment cannot be replaced.'
                );
            }

            if (
                $topUp->isDirty('credited_transaction_id')
                && $topUp->getOriginal('credited_transaction_id') !== null
            ) {
                throw new LogicException(
                    'Wallet top-up credit transaction cannot be replaced.'
                );
            }
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Wallet top-ups cannot be deleted.'
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

    /**
     * The single selected payment for this top-up.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Payment records that point to this payable.
     */
    public function paymentRecords(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable')
            ->latest('id');
    }

    public function creditedTransaction(): BelongsTo
    {
        return $this->belongsTo(
            WalletTransaction::class,
            'credited_transaction_id',
        );
    }

    public function getPayableAmount(): int
    {
        return (int)$this->amount;
    }

    public function getPayableTitle(): string
    {
        return 'شارژ کیف پول';
    }

    public function canStartPayment(): bool
    {
        if (!$this->status->canStartPayment()) {
            return false;
        }

        if ($this->payment_id !== null) {
            return false;
        }

        if (
            $this->expires_at
            && $this->expires_at->isPast()
        ) {
            return false;
        }

        return true;
    }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }
}
