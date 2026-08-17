<?php

namespace App\Domain\Raffle\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Enums\DrawRunTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

class DrawRun extends Model
{
    protected $fillable = [
        'campaign_id',
        'type',
        'status',
        'algorithm',
        'seed_commitment',
        'seed_ciphertext',
        'seed_reveal',
        'eligible_ticket_count',
        'eligible_set_hash',
        'started_by_user_id',
        'started_at',
        'completed_at',
        'failed_at',
        'failure_reason',
        'metadata',
    ];

    protected $casts = [
        'type' => DrawRunTypeEnum::class,
        'status' => DrawRunStatusEnum::class,
        'eligible_ticket_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (DrawRun $run): void {
            if (blank($run->public_token)) {
                $run->public_token = (string)Str::ulid();
            }
        });

        static::updating(function (DrawRun $run): void {
            $immutableFields = [
                'campaign_id',
                'type',
                'algorithm',
                'seed_commitment',
                'seed_ciphertext',
                'started_by_user_id',
            ];

            if ($run->isDirty($immutableFields)) {
                throw new LogicException('Draw run identity and committed seed cannot be changed.');
            }

            if ($run->isDirty('status')) {
                $original = DrawRunStatusEnum::tryFrom((string)$run->getRawOriginal('status'));

                if (!$original || !$original->canTransitionTo($run->status)) {
                    throw new LogicException('Invalid draw run status transition.');
                }
            }

            if ($run->seed_reveal && $run->status !== DrawRunStatusEnum::COMPLETED) {
                throw new LogicException('Draw seed can only be revealed after successful completion.');
            }
        });

        static::deleting(function (): never {
            throw new LogicException('Draw runs cannot be deleted.');
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

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(DrawWinner::class, 'draw_run_id');
    }
}
