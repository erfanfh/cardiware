<?php

namespace App\Application\Seo\Support\Sitemap;

use Illuminate\Database\Eloquent\Builder;

class StableSitemapPaginator
{
    /**
     * @return array<int, int>
     */
    public function activeChunkPages(
        Builder $allItemsQuery,
        Builder $indexableQuery,
        int     $chunkSize
    ): array
    {
        $total = (clone $allItemsQuery)->count();

        if ($total <= 0) {
            return [];
        }

        $lastChunkPage = (int)ceil($total / $chunkSize);

        $activePages = [];

        for ($chunkPage = 1; $chunkPage <= $lastChunkPage; $chunkPage++) {
            $ids = $this->idsForChunkPage(clone $allItemsQuery, $chunkPage, $chunkSize);

            if (
                !empty($ids)
                && (clone $indexableQuery)->whereIn('id', $ids)->exists()
            ) {
                $activePages[] = $chunkPage;
            }
        }

        return $activePages;
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
        return (clone $allItemsQuery)
            ->select('id')
            ->orderBy('id')
            ->forPage($chunkPage, $chunkSize)
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->all();
    }
}
