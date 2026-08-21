<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletTransaction;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PointConversion extends Model
{
    protected $table = 'loyalty_point_conversions';

    protected $fillable = [
        'user_id',
        'point_account_id',
        'wallet_id',
        'point_ledger_entry_id',
        'wallet_transaction_id',
        'points',
        'wallet_amount',
        'points_per_unit',
        'wallet_amount_per_unit',
        'idempotency_key',
        'idempotency_fingerprint',
    ];

    protected $casts = [
        'points' => 'integer',
        'wallet_amount' => 'integer',
        'points_per_unit' => 'integer',
        'wallet_amount_per_unit' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (PointConversion $conversion): void {
            if (blank($conversion->public_token)) {
                $conversion->public_token = (string) Str::ulid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pointAccount(): BelongsTo
    {
        return $this->belongsTo(PointAccount::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function pointLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(PointLedgerEntry::class);
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}
