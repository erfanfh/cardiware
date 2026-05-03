<?php

namespace App\Domain\Taxonomy\Models;

use App\Domain\Content\Models\Post;
use App\Domain\Project\Models\Project;
use App\Domain\Seo\Traits\HasSeoMeta;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxonomyTerm extends Model
{
    use SoftDeletes, HasSeoMeta;

    protected $fillable = [
        'taxonomy',
        'title',
        'slug',
        'path',
        'parent_id',
        'description',
    ];

    protected $casts = [
        'taxonomy' => TaxonomyKeyEnum::class,
    ];

    /**
     * Get the route key name for Laravel route model binding.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'path';
    }

    /**
     * Parent term relation.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Children relation.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Slug history relation.
     *
     * @return HasMany
     */
    public function slugs(): HasMany
    {
        return $this->hasMany(TaxonomyTermSlug::class);
    }

    /**
     * Scope terms by taxonomy key.
     *
     * @param Builder $query
     * @param TaxonomyKeyEnum $taxonomy
     * @return Builder
     */
    public function scopeForTaxonomy(Builder $query, TaxonomyKeyEnum $taxonomy): Builder
    {
        return $query->where('taxonomy', $taxonomy->value);
    }

    /**
     * Scope post categories.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePostCategories(Builder $query): Builder
    {
        return $query->where('taxonomy', TaxonomyKeyEnum::POST_CATEGORY);
    }

    /**
     * Scope post tags.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePostTags(Builder $query): Builder
    {
        return $query->where('taxonomy', TaxonomyKeyEnum::POST_TAG);
    }

    /**
     * Scope project tags.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeProjectTags(Builder $query): Builder
    {
        return $query->where('taxonomy', TaxonomyKeyEnum::PROJECT_TAG);
    }

    /**
     * Whether the taxonomy of this term is hierarchical.
     *
     * @return bool
     */
    public function isHierarchical(): bool
    {
        return $this->taxonomy->isHierarchical();
    }

    /**
     * Posts related to this taxonomy term.
     *
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

    /**
     * Projects related to this taxonomy term.
     *
     * @return MorphToMany
     */
    public function projects(): MorphToMany
    {
        return $this->morphedByMany(
            Project::class,
            'taxonomable',
            'taxonomables',
            'taxonomy_term_id',
            'taxonomable_id'
        );
    }

    /**
     * Recursive children relation.
     *
     * @return HasMany
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Get current term id with all descendant term ids.
     *
     * @return array<int>
     */
    public function selfAndDescendantIds(): array
    {
        $this->loadMissing('childrenRecursive');

        $ids = [$this->id];

        foreach ($this->childrenRecursive as $child) {
            $ids = array_merge($ids, $child->selfAndDescendantIds());
        }

        return $ids;
    }

    public function postsIncludingDescendants(): Builder
    {
        $termIds = $this->isHierarchical()
            ? $this->selfAndDescendantIds()
            : [$this->id];

        return Post::query()
            ->whereHas('taxonomyTerms', function (Builder $query) use ($termIds): void {
                $query->whereIn('taxonomy_terms.id', $termIds);
            });
    }
}
