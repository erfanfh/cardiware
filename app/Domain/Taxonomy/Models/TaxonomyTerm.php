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
                'source' => 'slug',
                'unique' => true,
                'includeTrashed' => true,
                'onUpdate' => true,
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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($term) {
            if (empty(trim($term->slug ?? ''))) {
                $term->slug = $term->title;
            }
        });
    }
}
