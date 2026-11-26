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
        'video_thumbnail_url',
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
            ?: ($user?->display_name ?: 'کاربر Cardiware');
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

    /** تصویر شاخص ویدیو: کاور مدیرانتخابی یا null (فقط برای ویدیو). */
    public function videoThumbnailUrl(): ?string
    {
        $url = trim((string)$this->video_thumbnail_url);
        return $url !== '' && preg_match('#^https?://#i', $url) ? $url : null;
    }

    /** آدرس embed-ready ویدیو از لینکهای معمول آپارات/یوتیوب */
    public function videoEmbedUrl(): ?string
    {
        $url = trim((string) $this->video_url);
        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return null;
        }
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        if (in_array($host, ['aparat.com', 'www.aparat.com'], true)
            && preg_match('#^/(?:v/|video/video/embed/videohash/)([A-Za-z0-9_-]+)(?:/|$)#', $path, $match)) {
            return 'https://www.aparat.com/video/video/embed/videohash/' . $match[1] . '/vt/frame';
        }
        $videoId = null;
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str($parts['query'] ?? '', $query);
            $videoId = $path === '/watch' ? ($query['v'] ?? null) : null;
            if (preg_match('#^/(?:embed|shorts)/([A-Za-z0-9_-]+)(?:/|$)#', $path, $match)) {
                $videoId = $match[1];
            }
        } elseif (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = trim($path, '/');
        }

        return is_string($videoId) && preg_match('/^[A-Za-z0-9_-]{6,}$/', $videoId)
            ? 'https://www.youtube.com/embed/' . $videoId
            : null;
    }
}
