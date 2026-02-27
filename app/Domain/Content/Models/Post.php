<?php

namespace App\Domain\Content\Models;

use App\Application\Taxonomy\Actions\GenerateUniqueSlugAction;
use App\Domain\Audit\Traits\Auditable;
use App\Domain\Content\Enums\StatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Taxonomy\Concerns\HasTaxonomyTerms;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes, HasTaxonomyTerms, Auditable;

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
     * @param array|string|null $tags
     * @return array
     */
    public function resolveTagIds(array|string|null $tags): array
    {
        return collect($tags)->map(function ($tagName) {
            return TaxonomyTerm::firstOrCreate(
                ['title' => $tagName, 'type' => TaxonomyTermTypeEnum::TAG->value],
                ['slug' => (new GenerateUniqueSlugAction())->execute($tagName, TaxonomyTermTypeEnum::TAG)]
            )->id;
        })->all();
    }
}
