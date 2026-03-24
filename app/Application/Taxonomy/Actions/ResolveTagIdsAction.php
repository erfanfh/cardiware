<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Str;

class ResolveTagIdsAction
{
    /**
     * @param array $payload
     * @return array<int>
     */
    public function execute(array $payload): array
    {
        $existingIds = collect(data_get($payload, 'existing', []))
            ->map(fn($id) => (int)$id)
            ->filter()
            ->values();

        $validatedExistingIds = TaxonomyTerm::query()
            ->tags()
            ->whereIn('id', $existingIds)
            ->pluck('id');

        $newTitles = collect(data_get($payload, 'new', []))
            ->map(fn($title) => preg_replace('/\s+/', ' ', trim((string)$title)))
            ->filter()
            ->unique(fn($title) => mb_strtolower($title))
            ->values();

        $newIds = $newTitles->map(function (string $title) {
            $existing = TaxonomyTerm::query()
                ->tags()
                ->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])
                ->first();

            if ($existing) {
                return $existing->id;
            }

            $slug = $this->generateUniqueTagSlug($title);

            $term = TaxonomyTerm::query()->create([
                'type' => TaxonomyTermTypeEnum::TAG,
                'title' => $title,
                'slug' => $slug,
                'path' => null,
                'parent_id' => null,
                'description' => null,
            ]);

            return $term->id;
        });

        return $validatedExistingIds
            ->merge($newIds)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param string $title
     * @return string
     */
    protected function generateUniqueTagSlug(string $title): string
    {
        $baseSlug = Str::slug($title);

        if (blank($baseSlug)) {
            $baseSlug = 'tag';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
        TaxonomyTerm::query()
            ->where('slug', $slug)
            ->exists()
        ) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }

        return $slug;
    }
}
