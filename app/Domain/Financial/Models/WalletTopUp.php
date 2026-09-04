<?php

namespace App\Domain\Financial\Models;

use App\Application\Financial\Contracts\Payable;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\WalletTopUpPurposeEnum;
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
        'order_id',
        'wallet_hold_id',
        'request_key',
        'purpose',
        'status',
        'amount',
        'withdrawable_amount',
        'metadata',
        'expires_at',
    ];

    protected $casts = [
        'purpose' => WalletTopUpPurposeEnum::class,
        'status' => WalletTopUpStatusEnum::class,

        'amount' => 'integer',
        'withdrawable_amount' => 'integer',

        'metadata' => 'array',

        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (WalletTopUp $topUp): void {
            if (blank($topUp->public_token)) {
                $topUp->public_token = (string)Str::ulid();
            }

            if (blank($topUp->request_key)) {
                throw new LogicException(
                    'Wallet top-up request key is required.'
                );
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

            if ($topUp->status !== WalletTopUpStatusEnum::PENDING_PAYMENT) {
                throw new LogicException(
                    'New wallet top-up must be pending payment.'
                );
            }

            $topUp->assertPurposeIntegrity();
        });

        static::updating(function (WalletTopUp $topUp): void {
            $immutableFields = [
                'public_token',
                'request_key',
                'wallet_id',
                'order_id',
                'wallet_hold_id',
                'purpose',
                'amount',
                'withdrawable_amount',
            ];

            if ($topUp->isDirty($immutableFields)) {
                throw new LogicException(
                    'Wallet top-up financial fields cannot be updated.'
                );
            }

            if (
                $topUp->isDirty('payment_id')
                && $topUp->getOriginal('payment_id') !== null
            ) {
                throw new LogicException(
                    'Wallet top-up external payment cannot be replaced.'
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

            if (
                $topUp->isDirty('order_payment_id')
                && $topUp->getOriginal('order_payment_id') !== null
            ) {
                throw new LogicException(
                    'Wallet top-up order payment cannot be replaced.'
                );
            }

            if ($topUp->isDirty('status')) {
                $originalStatusValue = $topUp->getRawOriginal('status');

                $originalStatus = $originalStatusValue
                    ? WalletTopUpStatusEnum::tryFrom(
                        (string)$originalStatusValue,
                    )
                    : null;

                $newStatus = $topUp->status;

                if (
                    !$originalStatus
                    || !$originalStatus->canTransitionTo($newStatus)
                ) {
                    throw new LogicException(
                        'Invalid wallet top-up status transition.'
                    );
                }
            }

            $topUp->assertPurposeIntegrity();
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function walletHold(): BelongsTo
    {
        return $this->belongsTo(WalletHold::class, 'wallet_hold_id');
    }

    /**
     * External gateway payment used to charge this top-up.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * Payments whose payable is this wallet top-up.
     */
    public function paymentRecords(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable')->latest('id');
    }

    /**
     * Final wallet ledger credit generated from the external payment.
     */
    public function creditedTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'credited_transaction_id');
    }

    /**
     * Internal WALLET payment that completed the related order.
     */
    public function orderPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'order_payment_id');
    }

    public function getPayableAmount(): int
    {
        return (int)$this->amount;
    }

    /** Wallet top-ups are funding movements and are never taxed again. */
    public function getPayableTaxAmount(): int
    {
        return 0;
    }

    public function getPayableTitle(): string
    {
        return match ($this->purpose) {
            WalletTopUpPurposeEnum::MANUAL => 'شارژ کیف پول',
            WalletTopUpPurposeEnum::ORDER_PAYMENT => 'تکمیل موجودی برای پرداخت سفارش ' . ($this->order?->order_number ?? ''),
        };
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

    public function isManual(): bool
    {
        return $this->purpose->isManual();
    }

    public function isOrderPayment(): bool
    {
        return $this->purpose->isOrderPayment();
    }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }

    public function isFailed(): bool
    {
        return $this->status->isFailed();
    }

    private function assertPurposeIntegrity(): void
    {
        if ($this->purpose->isManual()) {
            if (
                $this->order_id !== null
                || $this->wallet_hold_id !== null
                || $this->order_payment_id !== null
            ) {
                throw new LogicException(
                    'Manual wallet top-up cannot be linked to an order payment.'
                );
            }

            return;
        }

        if ($this->purpose->isOrderPayment()) {
            if ($this->order_id === null || $this->wallet_hold_id === null) {
                throw new LogicException(
                    'Order payment wallet top-up requires an order and wallet hold.'
                );
            }

            /*
             * The shortage paid through the gateway must not become
             * withdrawable. It is immediately used for the order.
             */
            if ((int)$this->withdrawable_amount !== 0) {
                throw new LogicException(
                    'Order payment wallet top-up cannot be withdrawable.'
                );
            }
        }
    }
}
