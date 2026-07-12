<?php

namespace App\Domain\Financial\Models;

use App\Domain\Financial\Enums\WalletStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'status',
    ];

    protected $casts = [
        'status' => WalletStatusEnum::class,
        'balance' => 'integer',
        'withdrawable_balance' => 'integer',
        'held_balance' => 'integer',
        'held_withdrawable_balance' => 'integer',
    ];

    /**
     * Get wallet owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get available balance for purchases.
     */
    protected function availableBalance(): Attribute
    {
        return Attribute::make(
            get: fn(): int => (int)$this->balance
                - (int)$this->held_balance,
        );
    }

    /**
     * Get available withdrawable balance.
     */
    protected function availableWithdrawableBalance(): Attribute
    {
        return Attribute::make(
            get: fn(): int => (int)$this->withdrawable_balance
                - (int)$this->held_withdrawable_balance,
        );
    }

    /**
     * Get available non-withdrawable balance.
     */
    protected function availableNonWithdrawableBalance(): Attribute
    {
        return Attribute::make(
            get: fn(): int => (
                    (int)$this->balance
                    - (int)$this->held_balance
                ) - (
                    (int)$this->withdrawable_balance
                    - (int)$this->held_withdrawable_balance
                ),
        );
    }

    /**
     * Determine whether the wallet can be used by its owner.
     */
    public function allowsUserTransactions(): bool
    {
        return $this->status->allowsUserTransactions();
    }

    /**
     * Get wallet financial transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get wallet balance holds.
     */
    public function holds(): HasMany
    {
        return $this->hasMany(WalletHold::class);
    }

    /**
     * Get wallet top-up requests.
     */
    public function topUps(): HasMany
    {
        return $this->hasMany(WalletTopUp::class);
    }
}
