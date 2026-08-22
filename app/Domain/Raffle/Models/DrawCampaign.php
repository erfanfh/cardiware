<?php

namespace App\Domain\Raffle\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LogicException;

class DrawCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'body',
        'rules',
        'cover_media_file_id',
        'status',
        'starts_at',
        'closes_at',
        'draw_at',
        'ticket_limit',
        'ticket_limit_per_user',
        'allow_multiple_wins_per_user',
        'reserve_winners_per_prize',
        'winner_confirmation_window_hours',
        'show_winners_publicly',
        'created_by_user_id',
        'metadata',
    ];

    protected $casts = [
        'status' => DrawCampaignStatusEnum::class,
        'starts_at' => 'datetime',
        'closes_at' => 'datetime',
        'draw_at' => 'datetime',
        'ticket_limit' => 'integer',
        'ticket_limit_per_user' => 'integer',
        'allow_multiple_wins_per_user' => 'boolean',
        'reserve_winners_per_prize' => 'integer',
        'winner_confirmation_window_hours' => 'integer',
        'show_winners_publicly' => 'boolean',
        'issued_tickets_count' => 'integer',
        'active_tickets_count' => 'integer',
        'participants_count' => 'integer',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'drawing_started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (DrawCampaign $campaign): void {
            if (blank($campaign->public_token)) {
                $campaign->public_token = (string) Str::ulid();
            }
        });

        static::saving(function (DrawCampaign $campaign): void {
            $campaign->assertConfigurationIntegrity();
        });

        static::updating(function (DrawCampaign $campaign): void {
            if ($campaign->isDirty('status')) {
                $original = DrawCampaignStatusEnum::tryFrom(
                    (string) $campaign->getRawOriginal('status')
                );

                if (! $original || ! $original->canTransitionTo($campaign->status)) {
                    throw new LogicException('Invalid draw campaign status transition.');
                }
            }
        });

        static::deleting(function (DrawCampaign $campaign): void {
            if (
                $campaign->status !== DrawCampaignStatusEnum::DRAFT
                || (int) $campaign->issued_tickets_count > 0
            ) {
                throw new LogicException(
                    'Only empty draft draw campaigns can be deleted.'
                );
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_token';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function coverMediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'cover_media_file_id');
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(DrawPrize::class, 'campaign_id')->orderBy('sort_order');
    }

    public function ticketGrants(): HasMany
    {
        return $this->hasMany(TicketGrant::class, 'campaign_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(DrawTicket::class, 'campaign_id');
    }

    public function activeTickets(): HasMany
    {
        return $this->tickets()
            ->where('status', DrawTicketStatusEnum::ACTIVE);
    }

    public function drawRuns(): HasMany
    {
        return $this->hasMany(DrawRun::class, 'campaign_id')->latest('id');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(DrawWinner::class, 'campaign_id');
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(DrawCampaignEvent::class, 'campaign_id')->latest('occurred_at');
    }

    private function assertConfigurationIntegrity(): void
    {
        if ($this->starts_at && $this->closes_at && ! $this->starts_at->lt($this->closes_at)) {
            throw new LogicException('Draw campaign start date must be before its close date.');
        }

        if ($this->closes_at && $this->draw_at && $this->draw_at->lt($this->closes_at)) {
            throw new LogicException('Draw date cannot be before the campaign close date.');
        }

        if (! $this->closes_at && $this->starts_at && $this->draw_at && $this->draw_at->lt($this->starts_at)) {
            throw new LogicException('Draw date cannot be before the campaign start date.');
        }

        if ($this->ticket_limit !== null && (int) $this->ticket_limit <= 0) {
            throw new LogicException('Draw campaign ticket limit must be greater than zero or null.');
        }

        if ($this->ticket_limit_per_user !== null && (int) $this->ticket_limit_per_user <= 0) {
            throw new LogicException('Per-user ticket limit must be greater than zero or null.');
        }

        if (
            $this->ticket_limit !== null
            && $this->ticket_limit_per_user !== null
            && (int) $this->ticket_limit_per_user > (int) $this->ticket_limit
        ) {
            throw new LogicException('Per-user ticket limit cannot exceed total campaign ticket limit.');
        }

        if ((int) $this->reserve_winners_per_prize < 0) {
            throw new LogicException('Reserve winners per prize cannot be negative.');
        }

        if (
            $this->winner_confirmation_window_hours !== null
            && (int) $this->winner_confirmation_window_hours <= 0
        ) {
            throw new LogicException(
                'Winner confirmation window must be greater than zero or null.'
            );
        }
    }

    public function acceptsTicketsAt(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        if (! $this->status->acceptsTicketIssuance()) {
            return false;
        }

        if ($this->starts_at && $at->lt($this->starts_at)) {
            return false;
        }

        return ! $this->closes_at || $at->lt($this->closes_at);
    }

    public function hasReachedTicketLimit(): bool
    {
        return $this->ticket_limit !== null
            && $this->active_tickets_count >= $this->ticket_limit;
    }

    public function remainingTicketCapacity(): ?int
    {
        if ($this->ticket_limit === null) {
            return null;
        }

        return max(0, $this->ticket_limit - $this->active_tickets_count);
    }

    public function isPubliclyVisible(): bool
    {
        return in_array($this->status, [
            DrawCampaignStatusEnum::SCHEDULED,
            DrawCampaignStatusEnum::OPEN,
            DrawCampaignStatusEnum::CLOSED,
            DrawCampaignStatusEnum::DRAWING,
            DrawCampaignStatusEnum::COMPLETED,
        ], true);
    }

    protected function isCurrentlyOpen(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->acceptsTicketsAt(),
        );
    }
}
