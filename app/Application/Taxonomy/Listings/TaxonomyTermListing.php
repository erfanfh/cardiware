<?php

namespace App\Application\Taxonomy\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class TaxonomyTermListing extends BaseListing
{
    /**
     * @return array<int, string>
     */
    public function filters(): array
    {
        return [
            'search',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function allowedSorts(): array
    {
        return [
            'created_at' => 'created_at',
            'title' => 'title',
        ];
    }

    protected function filterSearch(mixed $value): void
    {
        $search = trim((string)$value);

        if ($search === '') {
            return;
        }

        $this->query->where(function ($query) use ($search) {
            $query
                ->where('title', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
        });
    }
}
