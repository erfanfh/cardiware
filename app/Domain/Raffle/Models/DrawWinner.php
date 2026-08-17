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
        'confirmed_at',
        'confirmed_by_user_id',
        'rejected_at',
        'rejected_by_user_id',
        'rejection_reason',
        'promoted_at',
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
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'promoted_at' => 'datetime',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
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
}
