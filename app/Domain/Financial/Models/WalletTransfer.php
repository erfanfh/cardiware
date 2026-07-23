<?php

namespace App\Domain\Financial\Models;

use App\Domain\Financial\Enums\WalletTransferStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WalletTransfer extends Model
{
    protected $fillable = [
        'public_token',

        'sender_user_id',
        'recipient_user_id',

        'sender_wallet_id',
        'recipient_wallet_id',

        'amount',
        'status',
        'request_key',

        'debit_transaction_id',
        'credit_transaction_id',

        'description',
        'metadata',

        'completed_at',
        'failed_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => WalletTransferStatusEnum::class,
            'metadata' => 'array',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WalletTransfer $transfer): void {
            if (blank($transfer->public_token)) {
                $transfer->public_token = (string)Str::uuid();
            }
        });
    }

    /**
     * User sending the wallet balance.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /**
     * User receiving the wallet balance.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Source wallet.
     */
    public function senderWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'sender_wallet_id');
    }

    /**
     * Destination wallet.
     */
    public function recipientWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'recipient_wallet_id');
    }

    /**
     * Debit ledger transaction.
     */
    public function debitTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'debit_transaction_id');
    }

    /**
     * Credit ledger transaction.
     */
    public function creditTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'credit_transaction_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', WalletTransferStatusEnum::COMPLETED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', WalletTransferStatusEnum::PENDING);
    }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }
}
