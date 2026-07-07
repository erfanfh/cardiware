<?php

namespace App\Domain\Support\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    protected $fillable = [
        'number',
        'user_id',
        'title',
        'status',
        'assigned_to_user_id',
        'last_message_at',
        'last_user_message_at',
        'last_support_message_at',
        'closed_at',
        'closed_by_user_id',
    ];

    protected $casts = [
        'status' => TicketStatusEnum::class,
        'last_message_at' => 'datetime',
        'last_user_message_at' => 'datetime',
        'last_support_message_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function visibleMessages(): HasMany
    {
        return $this->messages()
            ->where('is_internal_note', false);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(TicketMessage::class)
            ->latestOfMany();
    }

    public function latestVisibleMessage(): HasOne
    {
        return $this->hasOne(TicketMessage::class)
            ->where('is_internal_note', false)
            ->latestOfMany();
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', '!=', TicketStatusEnum::CLOSED->value);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', TicketStatusEnum::CLOSED->value);
    }

    public function scopeLatestActivity(Builder $query): Builder
    {
        return $query->orderByDesc('last_message_at')
            ->orderByDesc('id');
    }

    public function isClosed(): bool
    {
        return $this->status === TicketStatusEnum::CLOSED;
    }

    public function isOpen(): bool
    {
        return !$this->isClosed();
    }

    public function canReceiveReply(): bool
    {
        return $this->isOpen();
    }
}
