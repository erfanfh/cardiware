<?php

namespace App\Domain\Content\Models;

use App\Application\Taxonomy\Actions\GenerateUniqueSlugAction;
use App\Domain\Audit\Traits\Auditable;
use App\Domain\Content\Enums\StatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Taxonomy\Concerns\HasTaxonomyTerms;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
     * Sync post categories and record audit events.
     *
     * @param array<int, int|string> $categoryIds
     * @return array
     */
    public function syncCategoriesWithAudit(array $categoryIds): array
    {
        $result = $this->categories()->sync(
            collect($categoryIds)->mapWithKeys(fn($id) => [
                $id => ['type' => TaxonomyTermTypeEnum::CATEGORY->value]
            ])
        );

        $this->audit()->recordSync(
            $this,
            'taxonomy_terms',
            $result,
            'taxonomy_term_id',
            ['type' => 'category']
        );

        return $result;
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->morphToMany(TaxonomyTerm::class, 'taxonomable')
            ->withPivot('type')
            ->wherePivot('type', TaxonomyTermTypeEnum::CATEGORY->value)
            ->withTimestamps();
    }

    protected function audit(): AuditService
    {
        return app(AuditService::class);
    }

    /**
     * Sync tags and record audit events.
     *
     * @param array|string|null $tags
     * @return array
     */
    public function syncTagsWithAudit(array|string|null $tags): array
    {
        $result = $this->syncTags($tags);

        $this->audit()->recordSync(
            $this,
            'taxonomy_terms',
            $result,
            'taxonomy_term_id',
            ['type' => 'tag']
        );

        return $result;
    }

    /**
     * @param array|string|null $tags
     * @return array
     */
    public function syncTags(array|string|null $tags): array
    {
        $tagIds = collect($tags)->map(function ($tagName) {
            return TaxonomyTerm::firstOrCreate(
                ['title' => $tagName, 'type' => TaxonomyTermTypeEnum::TAG->value],
                ['slug' => (new GenerateUniqueSlugAction())->execute($tagName, TaxonomyTermTypeEnum::TAG)]
            )->id;
        });

        return $this->tags()->sync(
            $tagIds->mapWithKeys(fn($id) => [
                $id => ['type' => TaxonomyTermTypeEnum::TAG->value]
            ])
        );
    }

    /**
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->morphToMany(TaxonomyTerm::class, 'taxonomable')
            ->withPivot('type')
            ->wherePivot('type', TaxonomyTermTypeEnum::TAG->value)
            ->withTimestamps();
    }
}
