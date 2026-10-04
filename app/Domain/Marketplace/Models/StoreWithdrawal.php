<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\StoreWithdrawalStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreWithdrawal extends Model
{
    protected $fillable = [
        'store_id', 'requested_by_user_id', 'request_key', 'status', 'amount',
        'iban', 'card_number', 'account_holder', 'seller_note',
    ];

    protected $casts = [
        'status' => StoreWithdrawalStatusEnum::class,
        'amount' => 'integer',
        'iban' => 'encrypted',
        'card_number' => 'encrypted',
        'account_holder' => 'encrypted',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class)->withTrashed();
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id')->withTrashed();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id')->withTrashed();
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id')->withTrashed();
    }

    public function maskedIban(): string
    {
        return substr($this->iban, 0, 6).str_repeat('*', 16).substr($this->iban, -4);
    }

    public function maskedCardNumber(): string
    {
        return '**** **** **** '.substr($this->card_number, -4);
    }
}
