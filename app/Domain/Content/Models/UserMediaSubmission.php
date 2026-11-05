<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\UserMediaSubmissionStatusEnum;
use App\Domain\Content\Enums\UserMediaSubmissionTypeEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMediaSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'image_path',
        'image_url',
        'video_url',
        'order_number',
        'purchaser_mobile',
        'status',
        'approved_at',
        'points_awarded',
    ];

    protected $casts = [
        'type' => UserMediaSubmissionTypeEnum::class,
        'status' => UserMediaSubmissionStatusEnum::class,
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', UserMediaSubmissionStatusEnum::APPROVED->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', UserMediaSubmissionStatusEnum::PENDING->value);
    }

    public function submitterName(): string
    {
        $user = $this->user;

        return trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? ''))
            ?: ($user?->display_name ?: 'کاربر مودینگ‌وی');
    }

    /**
     * لینک اصلی رسانه (فایل تصویر یا ویدیو) برای مشاهده و دانلود در پنل.
     * فقط http(s) برگردانده می‌شود تا لینک‌هایی مثل javascript: هرگز قابل کلیک نباشند.
     */
    public function mediaUrl(): ?string
    {
        $candidates = [
            $this->video_url,
            $this->image_url,
            $this->image_path ? asset($this->image_path) : null,
        ];

        foreach ($candidates as $candidate) {
            $url = trim((string) $candidate);
            if ($url !== '' && preg_match('#^https?://#i', $url)) {
                return $url;
            }
        }

        return null;
    }

    /** آدرس embed-ready ویدیو از لینکهای معمول آپارات/یوتیوب */
    public function videoEmbedUrl(): ?string
    {
        $url = trim((string)$this->video_url);

        if ($url === '') {
            return null;
        }

        // آپارات: https://www.aparat.com/v/XXXX → https://www.aparat.com/video/video/embed/videohash/XXXX/vt/frame
        if (preg_match('#aparat\.com/v/([A-Za-z0-9_-]+)#', $url, $m)) {
            return 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame';
        }

        // یوتیوب: watch?v= یا youtu.be/
        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        return $url;
    }
}
