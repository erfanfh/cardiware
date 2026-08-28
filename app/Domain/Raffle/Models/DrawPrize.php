<?php

namespace App\Domain\Raffle\Models;

use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

class DrawPrize extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'title',
        'description',
        'delivery_description',
        'estimated_value',
        'media_file_id',
        'quantity',
        'reserve_winners_count',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'estimated_value' => 'integer',
        'reserve_winners_count' => 'integer',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (DrawPrize $prize): void {
            if ((int)$prize->quantity <= 0) {
                throw new LogicException('Draw prize quantity must be greater than zero.');
            }

            if (
                $prize->reserve_winners_count !== null
                && (int)$prize->reserve_winners_count < 0
            ) {
                throw new LogicException('Prize reserve winner count cannot be negative.');
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DrawCampaign::class, 'campaign_id');
    }

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'media_file_id');
    }

    public function effectiveReserveWinnersCount(): int
    {
        return (int)($this->reserve_winners_count
            ?? $this->campaign?->reserve_winners_per_prize
            ?? 0);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(DrawWinner::class, 'prize_id');
    }
}
