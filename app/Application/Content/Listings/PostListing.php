<?php

namespace App\Application\Content\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class PostListing extends BaseListing
{
    /**
     * @return array<int, string>
     */
    public function filters(): array
    {
        return [
            'id',
            'title',
            'status',
            'author_ids',
            'tag_ids',
            'category_ids',
            'published_at_from',
            'published_at_to',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function allowedSorts(): array
    {
        return [
            'published_at' => 'published_at',
        ];
    }

    protected function filterId(mixed $value): void
    {
        $this->query->where('id', $value);
    }

    protected function filterTitle(mixed $value): void
    {
        $this->query->where('title', 'like', '%' . $value . '%');
    }

    protected function filterStatus(mixed $value): void
    {
        $this->query->where('status', $value);
    }

    protected function filterTagIds(mixed $value): void
    {
        $tagIds = array_filter((array)$value, static fn($id) => filled($id));

        if ($tagIds === []) {
            return;
        }

        $this->query->whereHas('tags', function ($query) use ($tagIds) {
            $query->whereIn('taxonomy_terms.id', $tagIds);
        });
    }

    protected function filterPublishedAtFrom(mixed $value): void
    {
        $this->query->whereDate('published_at', '>=', $value);
    }

    protected function filterPublishedAtTo(mixed $value): void
    {
        $this->query->whereDate('published_at', '<=', $value);
    }
}
