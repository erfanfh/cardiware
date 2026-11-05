<?php

namespace App\Domain\Marketplace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** جایگاه «فروشگاه ویژه» صفحه اصلی؛ source: reward (خودکار، جایزه) یا admin (انتخاب مدیر). */
class StoreSpotlight extends Model
{
    protected $fillable = [
        'store_id', 'store_reward_id', 'source', 'headline', 'starts_at', 'ends_at', 'created_by_user_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(StoreReward::class, 'store_reward_id');
    }

    public function scopeActive(Builder $query, $at = null): Builder
    {
        $at ??= now();

        return $query->where('starts_at', '<=', $at)->where('ends_at', '>', $at);
    }
}
