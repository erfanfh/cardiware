<?php

namespace App\Domain\Content\Models;

use App\Domain\Audit\Traits\Auditable;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Feature\Traits\HasFeatures;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\System\Interfaces\PageViewableInterface;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Traits\HasTaxonomyTerms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model implements PageViewableInterface
{
    use SoftDeletes, HasTaxonomyTerms, Auditable, HasFeatures;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'picture_id',
        'published_at',
    ];

    protected $casts = [
        'status' => PostStatusEnum::class,
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
     * @return BelongsTo
     */
    public function picture(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    /**
     * @param array|string|null $tags
     * @return array
     */
    public function resolveTagIds(array|string|null $tags): array
    {
        return collect($tags)->map(function ($tagName) {
            return TaxonomyTerm::firstOrCreate(
                ['title' => $tagName, 'type' => TaxonomyTermTypeEnum::TAG->value],
//                ['slug' => (new GenerateUniqueSlugAction())->execute($tagName, TaxonomyTermTypeEnum::TAG)]
            )->id;
        })->all();
    }

    /**
     * @return bool
     */
    public function isPubliclyVisible(): bool
    {
        return $this->status === PostStatusEnum::ACTIVE;
    }

    /**
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return url($this->slug ?? '/');
    }
}
