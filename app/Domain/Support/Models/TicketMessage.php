<?php

namespace App\Domain\Support\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'sender_type',
        'body',
        'is_internal_note',
    ];

    protected $casts = [
        'sender_type' => TicketMessageSenderTypeEnum::class,
        'is_internal_note' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFromUser(): bool
    {
        return $this->sender_type === TicketMessageSenderTypeEnum::USER;
    }

    public function isFromSupport(): bool
    {
        return $this->sender_type === TicketMessageSenderTypeEnum::SUPPORT;
    }

    public function isSystemMessage(): bool
    {
        return $this->sender_type === TicketMessageSenderTypeEnum::SYSTEM;
    }

    public function isVisibleToUser(): bool
    {
        return !$this->is_internal_note;
    }
}
