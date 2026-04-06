<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\PageStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\System\Interfaces\PageViewableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model implements PageViewableInterface
{
    use SoftDeletes;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'picture_id',
        'template',
        'published_at',
    ];

    protected $casts = [
        'status' => PageStatusEnum::class,
    ];

    /**
     * @return MorphMany
     */
    public function slugs(): MorphMany
    {
        return $this->morphMany(ContentSlug::class, 'sluggable');
    }

    /**
     * Get the route key name for Laravel route model binding.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return bool
     */
    public function isPubliclyVisible(): bool
    {
        return $this->status === PageStatusEnum::ACTIVE;
    }

    /**
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return url($this->slug ?? '/');
    }

    /**
     * @return BelongsTo
     */
    public function picture(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }
}
