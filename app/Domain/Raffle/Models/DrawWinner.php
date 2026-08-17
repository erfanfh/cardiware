<?php

namespace App\Domain\Raffle\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class DrawWinner extends Model
{
    protected $fillable = [
        'campaign_id',
        'draw_run_id',
        'prize_id',
        'ticket_id',
        'user_id',
        'kind',
        'position',
        'status',
        'selected_at',
        'confirmation_deadline_at',
        'confirmed_at',
        'confirmed_by_user_id',
        'rejected_at',
        'rejected_by_user_id',
        'rejection_reason',
        'expired_at',
        'promoted_at',
        'promoted_for_position',
        'promoted_by_user_id',
        'replaces_winner_id',
        'delivered_at',
        'delivered_by_user_id',
        'delivery_note',
        'metadata',
    ];

    protected $casts = [
        'kind' => DrawWinnerKindEnum::class,
        'status' => DrawWinnerStatusEnum::class,
        'position' => 'integer',
        'selected_at' => 'datetime',
        'confirmation_deadline_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expired_at' => 'datetime',
        'promoted_at' => 'datetime',
        'promoted_for_position' => 'integer',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (DrawWinner $winner): void {
            $immutableFields = [
                'campaign_id',
                'draw_run_id',
                'prize_id',
                'ticket_id',
                'user_id',
                'kind',
                'position',
                'selected_at',
            ];

            if ($winner->isDirty($immutableFields)) {
                throw new LogicException('Draw winner selection identity cannot be changed.');
            }

            if ($winner->isDirty('confirmation_deadline_at')) {
                $isFirstPromotion = $winner->isDirty('promoted_at')
                    && $winner->getOriginal('promoted_at') === null
                    && $winner->promoted_at !== null;

                if (!$isFirstPromotion) {
                    throw new LogicException('Winner confirmation deadline can only change during reserve promotion.');
                }
            }
        });

        static::deleting(function (): never {
            throw new LogicException('Draw winner records cannot be deleted.');
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DrawCampaign::class, 'campaign_id');
    }

    public function drawRun(): BelongsTo
    {
        return $this->belongsTo(DrawRun::class, 'draw_run_id');
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(DrawPrize::class, 'prize_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(DrawTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    public function promoter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by_user_id');
    }

    public function replacedWinner(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_winner_id');
    }

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by_user_id');
    }

    public function isEffectivePrimary(): bool
    {
        return $this->kind === DrawWinnerKindEnum::PRIMARY || $this->promoted_at !== null;
    }

    public function effectivePrimaryPosition(): ?int
    {
        if ($this->kind === DrawWinnerKindEnum::PRIMARY) {
            return (int)$this->position;
        }

        return $this->promoted_for_position !== null
            ? (int)$this->promoted_for_position
            : null;
    }

    public function hasReplacement(): bool
    {
        return self::query()->where('replaces_winner_id', $this->getKey())->exists();
    }

    public function replacement(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(self::class, 'replaces_winner_id');
    }
}
