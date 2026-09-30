<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Commerce\Enums\OrderRefundMethodEnum;
use App\Domain\Financial\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRefund extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'wallet_transaction_id',
        'processed_by_user_id',
        'manual_reference',
    ];

    protected $casts = [
        'method' => OrderRefundMethodEnum::class,
        'amount' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}
