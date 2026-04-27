<?php

namespace App\Application\Seo\Support\Sitemap;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StableSitemapPaginator
{
    private function numberedQuery(Builder $query, int $chunkSize): Builder
    {
        return (clone $query)
            ->select('id')
            ->selectRaw(
                'CEIL(ROW_NUMBER() OVER (ORDER BY id) / ?) as sitemap_page',
                [$chunkSize]
            );
    }

    /**
     * @return array<int, int>
     */
    public function activeChunkPages(
        Builder $allItemsQuery,
        Builder $indexableQuery,
        int     $chunkSize
    ): array
    {
        $pages = DB::query()
            ->fromSub($this->numberedQuery($allItemsQuery, $chunkSize), 'items')
            ->select('sitemap_page')
            ->distinct()
            ->orderBy('sitemap_page')
            ->pluck('sitemap_page')
            ->map(fn($page) => (int)$page)
            ->all();

        return collect($pages)
            ->filter(function (int $chunkPage) use ($allItemsQuery, $indexableQuery, $chunkSize) {
                $ids = $this->idsForChunkPage(clone $allItemsQuery, $chunkPage, $chunkSize);

                return !empty($ids)
                    && (clone $indexableQuery)->whereIn('id', $ids)->exists();
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function publicPages(
        Builder $allItemsQuery,
        Builder $indexableQuery,
        int     $chunkSize
    ): array
    {
        $count = count($this->activeChunkPages($allItemsQuery, $indexableQuery, $chunkSize));

        return $count > 0 ? range(1, $count) : [];
    }

    /**
     * @return array<int, int>
     */
    public function idsForPublicPage(
        Builder $allItemsQuery,
        Builder $indexableQuery,
        int     $publicPage,
        int     $chunkSize
    ): array
    {
        $chunkPages = $this->activeChunkPages($allItemsQuery, $indexableQuery, $chunkSize);

        $realChunkPage = $chunkPages[$publicPage - 1] ?? null;

        if (!$realChunkPage) {
            return [];
        }

        return $this->idsForChunkPage($allItemsQuery, $realChunkPage, $chunkSize);
    }

    /**
     * @return array<int, int>
     */
    private function idsForChunkPage(Builder $allItemsQuery, int $chunkPage, int $chunkSize): array
    {
        return DB::query()
            ->fromSub($this->numberedQuery($allItemsQuery, $chunkSize), 'items')
            ->where('sitemap_page', $chunkPage)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->all();
    }
}
