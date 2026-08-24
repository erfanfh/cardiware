<?php

namespace App\Domain\Video\Models;

use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Traits\HasComments;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Seo\Traits\HasSeoMeta;
use App\Domain\System\Interfaces\PageViewableInterface;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Traits\HasTaxonomyTerms;
use App\Domain\Video\Enums\VideoSourceTypeEnum;
use App\Domain\Video\Enums\VideoStatusEnum;
use App\Domain\Video\Scopes\ActiveVideoScope;
use App\Infrastructure\Traits\HasExcerpt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model implements PageViewableInterface, CommentableContract
{
    use SoftDeletes, HasTaxonomyTerms, HasComments, HasSeoMeta, HasExcerpt;

    protected $fillable = [
        'author_id', 'title', 'slug', 'excerpt', 'body', 'status', 'source_type',
        'video_url', 'aparat_video_id', 'cover_media_file_id', 'duration_seconds',
        'is_featured', 'comments_enabled', 'guest_comments_enabled', 'published_at',
    ];

    protected $casts = [
        'status' => VideoStatusEnum::class,
        'source_type' => VideoSourceTypeEnum::class,
        'duration_seconds' => 'integer',
        'is_featured' => 'boolean',
        'comments_enabled' => 'boolean',
        'guest_comments_enabled' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ActiveVideoScope());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'cover_media_file_id');
    }

    public function slugs(): HasMany
    {
        return $this->hasMany(VideoSlug::class);
    }

    public function categories(): MorphToMany
    {
        return $this->taxonomyTermsFor(TaxonomyKeyEnum::VIDEO_CATEGORY);
    }

    public function tags(): MorphToMany
    {
        return $this->taxonomyTermsFor(TaxonomyKeyEnum::VIDEO_TAG);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === VideoStatusEnum::ACTIVE && (bool)$this->published_at?->isPast();
    }

    public function getUrlAttribute(): string
    {
        return route('videos.show', $this->slug);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        return $this->source_type === VideoSourceTypeEnum::APARAT && filled($this->aparat_video_id)
            ? 'https://www.aparat.com/video/video/embed/videohash/' . rawurlencode($this->aparat_video_id) . '/vt/frame'
            : null;
    }

    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration_seconds) {
            return null;
        }

        $hours = intdiv($this->duration_seconds, 3600);
        $minutes = intdiv($this->duration_seconds % 3600, 60);
        $seconds = $this->duration_seconds % 60;

        return $hours > 0
            ? sprintf('%d:%02d:%02d', $hours, $minutes, $seconds)
            : sprintf('%d:%02d', $minutes, $seconds);
    }

    public function commentsEnabled(): bool
    {
        return $this->comments_enabled;
    }

    public function guestCommentsEnabled(): bool
    {
        return $this->guest_comments_enabled;
    }

    public function requiresCommentRating(): bool
    {
        return false;
    }
}
