<?php

namespace App\Domain\Project\Models;

use App\Domain\Media\Models\MediaFile;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Taxonomy\Concerns\HasTaxonomyTerms;
use App\Infrastructure\Traits\HasExcerpt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasTaxonomyTerms, HasExcerpt, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'status',
        'cover_id',
        'progress_percent',
        'current_version',
        'creator_name',
        'platform',
        'excerpt',
        'body',
        'last_updated_at',
        'released_at',
    ];

    protected $casts = [
        'status' => ProjectStatusEnum::class,
    ];

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
    public function cover(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    /**
     * @return HasMany
     */
    public function changelogs(): HasMany
    {
        return $this->hasMany(ProjectChangelog::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany
     */
    public function roadmapItems(): HasMany
    {
        return $this->hasMany(ProjectRoadmapItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany
     */
    public function ads(): HasMany
    {
        return $this->hasMany(ProjectAd::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany
     */
    public function features(): HasMany
    {
        return $this->hasMany(ProjectFeature::class)->orderBy('sort_order');
    }
}
