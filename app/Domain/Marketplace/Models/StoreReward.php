<?php

namespace App\Domain\Marketplace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreReward extends Model
{
    public const PERIOD_LABELS = ['week' => 'برترین فروشنده هفته', 'month' => 'برترین فروشنده ماه', 'year' => 'برترین فروشنده سال'];

    protected $fillable = [
        'closure_id', 'store_id', 'period_type', 'period_key', 'period_label', 'rank', 'net_sales', 'orders_count',
        'commission_bonus_bp', 'bonus_starts_at', 'bonus_ends_at', 'coupon_id', 'coupon_code',
    ];

    protected $casts = [
        'rank' => 'integer',
        'net_sales' => 'integer',
        'orders_count' => 'integer',
        'commission_bonus_bp' => 'integer',
        'bonus_starts_at' => 'datetime',
        'bonus_ends_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /** جوایز کمیسیونی که همین الان برای فروشگاه فعال‌اند. */
    public function scopeActiveBonusFor(Builder $query, int $storeId, $at = null): Builder
    {
        $at ??= now();

        return $query->where('store_id', $storeId)
            ->where('commission_bonus_bp', '>', 0)
            ->where('bonus_starts_at', '<=', $at)
            ->where('bonus_ends_at', '>', $at);
    }

    public function title(): string
    {
        $places = [1 => 'نفر اول', 2 => 'نفر دوم', 3 => 'نفر سوم'];

        return (self::PERIOD_LABELS[$this->period_type] ?? 'برترین فروشنده') . ' — '
            . ($places[$this->rank] ?? 'نفر ' . $this->rank) . " ({$this->period_label})";
    }

    public function trophy(): string
    {
        return [1 => '🏆', 2 => '🥈', 3 => '🥉'][$this->rank] ?? '🏅';
    }
}
