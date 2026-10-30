<?php

namespace App\Domain\Story\Models;

use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Story extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'caption', 'image_id', 'thumbnail_id', 'link_url', 'link_label',
        'duration_seconds', 'sort_order', 'is_active', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_seconds' => 'integer',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_id');
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'thumbnail_id');
    }

    public function displayStatus(): string
    {
        return match (true) {
            ! $this->is_active => 'غیرفعال',
            $this->ends_at !== null && $this->ends_at->lessThanOrEqualTo(now()) => 'پایان‌یافته',
            $this->starts_at !== null && $this->starts_at->isFuture() => 'زمان‌بندی‌شده',
            ! $this->image || $this->image->type->value !== 'image'
                || $this->image->visibility->value !== 'public' || $this->image->status->value !== 'ready'
                || ! $this->image->primaryVariant => 'تصویر نامعتبر',
            default => 'در حال نمایش',
        };
    }
}
