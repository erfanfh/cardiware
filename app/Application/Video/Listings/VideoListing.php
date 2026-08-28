<?php

namespace App\Application\Video\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class VideoListing extends BaseListing
{
    public function filters(): array
    {
        return [
            'id',
            'title',
            'status',
            'source_type',
            'author_ids',
            'tag_ids',
            'category_ids',
            'published_at_from',
            'published_at_to',
        ];
    }

    protected function allowedSorts(): array
    {
        return ['published_at' => 'published_at'];
    }

    protected function filterId(mixed $value): void
    {
        $this->query->where('id', $value);
    }

    protected function filterTitle(mixed $value): void
    {
        $this->query->where('title', 'like', '%'.$value.'%');
    }

    protected function filterStatus(mixed $value): void
    {
        $this->query->where('status', $value);
    }

    protected function filterSourceType(mixed $value): void
    {
        $this->query->where('source_type', $value);
    }

    protected function filterAuthorIds(mixed $value): void
    {
        $ids = array_filter((array) $value, static fn ($id) => filled($id));

        if ($ids !== []) {
            $this->query->whereIn('author_id', $ids);
        }
    }

    protected function filterTagIds(mixed $value): void
    {
        $this->filterTaxonomyRelation('tags', $value);
    }

    protected function filterCategoryIds(mixed $value): void
    {
        $this->filterTaxonomyRelation('categories', $value);
    }

    protected function filterPublishedAtFrom(mixed $value): void
    {
        $this->query->whereDate('published_at', '>=', $value);
    }

    protected function filterPublishedAtTo(mixed $value): void
    {
        $this->query->whereDate('published_at', '<=', $value);
    }

    private function filterTaxonomyRelation(string $relation, mixed $value): void
    {
        $ids = array_filter((array) $value, static fn ($id) => filled($id));

        if ($ids === []) {
            return;
        }

        $this->query->whereHas($relation, fn ($query) => $query->whereIn('taxonomy_terms.id', $ids));
    }
}
