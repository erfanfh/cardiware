<?php

namespace App\Domain\Raffle\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use LogicException;

class TicketGrant extends Model
{
    protected $table = 'draw_ticket_grants';

    public const UPDATED_AT = null;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'quantity',
        'source_type',
        'source_id',
        'idempotency_key',
        'idempotency_fingerprint',
        'issued_by_user_id',
        'issued_at',
        'invalidated_at',
        'invalidated_by_user_id',
        'invalidation_reason',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'issued_at' => 'datetime',
        'invalidated_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (TicketGrant $grant): void {
            if (blank($grant->public_token)) {
                $grant->public_token = (string)Str::ulid();
            }

            if ((int)$grant->quantity <= 0) {
                throw new LogicException('Ticket grant quantity must be greater than zero.');
            }
        });

        static::updating(function (TicketGrant $grant): void {
            $immutableFields = [
                'campaign_id',
                'user_id',
                'quantity',
                'source_type',
                'source_id',
                'idempotency_key',
                'idempotency_fingerprint',
                'issued_by_user_id',
                'issued_at',
            ];

            if ($grant->isDirty($immutableFields)) {
                throw new LogicException('Issued ticket grant fields cannot be changed.');
            }
        });

        static::deleting(function (): never {
            throw new LogicException('Ticket grants cannot be deleted.');
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function invalidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invalidated_by_user_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(DrawTicket::class, 'ticket_grant_id');
    }

    public function activeTickets(): HasMany
    {
        return $this->tickets()
            ->where('status', \App\Domain\Raffle\Enums\DrawTicketStatusEnum::ACTIVE);
    }

    public function isInvalidated(): bool
    {
        return $this->invalidated_at !== null;
    }
}
