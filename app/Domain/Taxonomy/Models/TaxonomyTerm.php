<?php

namespace App\Domain\Taxonomy\Models;

use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxonomyTerm extends Model
{
    use Sluggable, SoftDeletes;

    protected $fillable = [
        'type',
        'title',
        'slug',
        'path',
        'parent_id',
        'description',
    ];

    protected $casts = [
        'type' => TaxonomyTermTypeEnum::class,
    ];

    /**
     * configs of sluggable package
     *
     * @return array[]
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true,
                'includeTrashed' => true
            ]
        ];
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
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function slugs(): HasMany
    {
        return $this->hasMany(TaxonomyTermSlug::class);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeCategory(Builder $query): Builder
    {
        return $query->where('type', TaxonomyTermTypeEnum::CATEGORY);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeTag(Builder $query): Builder
    {
        return $query->where('type', TaxonomyTermTypeEnum::TAG);
    }

    /**
     * @return MorphToMany
     */
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(
            Post::class,
            'taxonomable',
            'taxonomables',
            'taxonomy_term_id',
            'taxonomable_id'
        );
    }

    public function generatePath(): string
    {
        if (!$this->parent) {
            return $this->slug;
        }

        return $this->parent->generatePath() . '/' . $this->slug;
    }

    /**
     * regenerate itself + children
     */
    public function regeneratePathAndDescendants(): void
    {
        $this->path = $this->generatePath();
        $this->saveQuietly();

        $this->children()->each(function (self $child) {
            $child->regeneratePathAndDescendants();
        });
    }

    /**
     * save history of slug when it changes.
     */
    public function recordSlugHistory(string $oldSlug): void
    {
        if (empty($oldSlug) || $oldSlug === $this->slug) {
            return;
        }

        $this->slugs()->where('is_current', true)->update(['is_current' => false]);

        $this->slugs()->updateOrCreate(
            [
                'route_key' => $oldSlug,
                'type' => $this->type->value,
            ],
            [
                'is_current' => false,
                'taxonomy_term_id' => $this->id,
            ]
        );

        $this->slugs()->updateOrCreate(
            [
                'route_key' => $this->slug,
                'type' => $this->type->value,
            ],
            [
                'is_current' => true,
                'taxonomy_term_id' => $this->id,
            ]
        );
    }
}
