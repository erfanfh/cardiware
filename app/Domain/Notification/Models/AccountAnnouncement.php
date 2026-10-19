<?php

namespace App\Domain\Notification\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AccountAnnouncement extends Model
{
    public const TYPES = ['info' => 'اطلاع‌رسانی', 'warning' => 'مهم', 'success' => 'خبر خوب'];

    protected $fillable = ['title', 'body', 'type', 'is_active', 'starts_at', 'ends_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(fn(Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn(Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $now));
    }

    public function statusLabel(): string
    {
        if (!$this->is_active) {
            return 'غیرفعال';
        }
        if ($this->ends_at?->lte(now())) {
            return 'پایان‌یافته';
        }
        if ($this->starts_at?->isFuture()) {
            return 'زمان‌بندی‌شده';
        }

        return 'در حال نمایش';
    }
}
