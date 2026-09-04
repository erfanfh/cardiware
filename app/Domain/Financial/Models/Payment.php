<?php

namespace App\Domain\Financial\Models;

use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'gateway',
        'status',
        'amount',
        'tax_amount',
        'tracking_code',
        'gateway_reference',
        'gateway_transaction_id',
        'gateway_status',
        'gateway_message',
        'request_payload',
        'callback_payload',
        'verify_payload',
        'paid_at',
        'failed_at',
    ];

    protected $casts = [
        'gateway' => PaymentGatewayEnum::class,
        'status' => PaymentStatusEnum::class,
        'amount' => 'integer',
        'tax_amount' => 'integer',
        'request_payload' => 'array',
        'callback_payload' => 'array',
        'verify_payload' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (blank($payment->public_token)) {
                $payment->public_token = (string)Str::ulid();
            }
        });
    }

    /**
     * Get the payable model.
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Determine whether the payment is paid.
     */
    public function isPaid(): bool
    {
        return $this->status->isPaid();
    }

    /**
     * Determine whether the payment can be verified.
     */
    public function canBeVerified(): bool
    {
        return $this->status->canBeVerified();
    }
}
