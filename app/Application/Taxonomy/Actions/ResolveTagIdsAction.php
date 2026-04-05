<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ResolveTagIdsAction
{
    /**
     * @param array $payload
     * @return array<int>
     * @throws Throwable
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
            Gate::authorize('tag.manage');

            $existing = TaxonomyTerm::query()
                ->tags()
                ->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])
                ->first();

            if ($existing) {
                return $existing->id;
            }

            $term = app(CreateTagAction::class)->execute([
                'title' => $title,
                'slug' => $title,
            ]);

            return $term->id;
        });

        return $validatedExistingIds
            ->merge($newIds)
            ->unique()
            ->values()
            ->all();
    }
}
