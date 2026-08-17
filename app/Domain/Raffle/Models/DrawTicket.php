<?php

namespace App\Domain\Raffle\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

class DrawTicket extends Model
{
    protected $fillable = [
        'campaign_id',
        'ticket_grant_id',
        'user_id',
        'code',
        'status',
        'issued_at',
        'invalidated_at',
        'invalidated_by_user_id',
        'invalidation_reason',
    ];

    protected $casts = [
        'status' => DrawTicketStatusEnum::class,
        'issued_at' => 'datetime',
        'invalidated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (DrawTicket $ticket): void {
            if (blank($ticket->public_token)) {
                $ticket->public_token = (string)Str::ulid();
            }
        });

        static::updating(function (DrawTicket $ticket): void {
            $immutableFields = [
                'campaign_id',
                'ticket_grant_id',
                'user_id',
                'code',
                'issued_at',
            ];

            if ($ticket->isDirty($immutableFields)) {
                throw new LogicException('Issued draw ticket identity cannot be changed.');
            }

            if ($ticket->isDirty('status')) {
                $original = DrawTicketStatusEnum::tryFrom(
                    (string)$ticket->getRawOriginal('status')
                );

                if (
                    $original !== DrawTicketStatusEnum::ACTIVE
                    || $ticket->status !== DrawTicketStatusEnum::INVALIDATED
                ) {
                    throw new LogicException('Invalid draw ticket status transition.');
                }
            }
        });

        static::deleting(function (): never {
            throw new LogicException('Draw tickets cannot be deleted.');
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_token';
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DrawCampaign::class, 'campaign_id');
    }

    public function grant(): BelongsTo
    {
        return $this->belongsTo(TicketGrant::class, 'ticket_grant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invalidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invalidated_by_user_id');
    }

    public function winnerRecords(): HasMany
    {
        return $this->hasMany(DrawWinner::class, 'ticket_id');
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}
