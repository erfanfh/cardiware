<?php

namespace App\Application\Project\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class ProjectListing extends BaseListing
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
            'progress_percent_from',
            'progress_percent_to',
            'released_at_from',
            'released_at_to',
            'last_updated_at_from',
            'last_updated_at_to',
            'tag_ids',
            'created_at_from',
            'created_at_to',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function allowedSorts(): array
    {
        return [
            'created_at' => 'created_at',
            'progress_percent' => 'progress_percent',
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

    protected function filterProgressPercentFrom(mixed $value): void
    {
        $this->query->where('progress_percent', '>=', $value);
    }

    protected function filterProgressPercentTo(mixed $value): void
    {
        $this->query->where('progress_percent', '<=', $value);
    }

    protected function filterReleasedAtFrom(mixed $value): void
    {
        $this->query->whereDate('released_at', '>=', $value);
    }

    protected function filterReleasedAtTo(mixed $value): void
    {
        $this->query->whereDate('released_at', '<=', $value);
    }

    protected function filterLastUpdatedAtFrom(mixed $value): void
    {
        $this->query->whereDate('last_updated_at', '>=', $value);
    }

    protected function filterLastUpdatedAtTo(mixed $value): void
    {
        $this->query->whereDate('last_updated_at', '<=', $value);
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

    protected function filterCreatedAtFrom(mixed $value): void
    {
        $this->query->whereDate('created_at', '>=', $value);
    }

    protected function filterCreatedAtTo(mixed $value): void
    {
        $this->query->whereDate('created_at', '<=', $value);
    }
}
