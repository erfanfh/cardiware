<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\StatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Taxonomy\Concerns\HasTaxonomyTerms;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use Sluggable, SoftDeletes, HasTaxonomyTerms;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'published_at',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    /**
     * @return MorphMany
     */
    public function slugs(): MorphMany
    {
        return $this->morphMany(ContentSlug::class, 'sluggable');
    }

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
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->morphToMany(TaxonomyTerm::class, 'taxonomable')->where('type', TaxonomyTermTypeEnum::CATEGORY->value)->withTimestamps();
    }

    // app/Models/Post.php
    public function syncTags(array|string $tags): static
    {
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
            $tags = array_filter($tags);
        }

        $tagIds = [];
        foreach ($tags as $tagName) {
            $tag = TaxonomyTerm::firstOrCreate(['title' => $tagName]);
            $tagIds[] = $tag->id;
        }

        $this->tags()->sync($tagIds);

        return $this;
    }
}
