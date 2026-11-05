<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyChampion extends Model
{
    public const PERIOD_LABELS = ['month' => 'برترین ماه', 'season' => 'برترین فصل', 'year' => 'کاربر برتر سال'];

    protected $fillable = [
        'closure_id', 'period_type', 'period_key', 'period_label', 'rank', 'user_id', 'score',
        'points_awarded', 'coupon_id', 'coupon_code', 'raffle_tickets',
        'grand_prize', 'grand_prize_status', 'grand_prize_delivered_at',
    ];

    protected $casts = [
        'rank' => 'integer',
        'score' => 'integer',
        'points_awarded' => 'integer',
        'raffle_tickets' => 'integer',
        'grand_prize_delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closure(): BelongsTo
    {
        return $this->belongsTo(LoyaltyPeriodClosure::class, 'closure_id');
    }

    /** مثل «برترین ماه — نفر اول (آبان ۱۴۰۵)» */
    public function title(): string
    {
        $places = [1 => 'نفر اول', 2 => 'نفر دوم', 3 => 'نفر سوم'];

        return (self::PERIOD_LABELS[$this->period_type] ?? 'برترین') . ' — '
            . ($places[$this->rank] ?? 'نفر ' . $this->rank) . " ({$this->period_label})";
    }

    public function trophy(): string
    {
        return match ($this->rank) {
            1 => $this->period_type === 'year' ? '👑' : '🏆',
            2 => '🥈',
            default => '🥉',
        };
    }
}
