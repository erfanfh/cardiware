<?php

namespace App\Application\Content\Services;

use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use Illuminate\Support\Facades\Cache;

final class ContentSlugCacheService
{
    public function forgetBySlug(string $slug): void
    {
        $slug = trim($slug);

        if ($slug === '') {
            return;
        }

        Cache::forget($this->getCacheKey($slug));
    }

    /**
     * @param array<int, string> $slugs
     */
    public function forgetMany(array $slugs): void
    {
        foreach ($slugs as $slug) {
            $this->forgetBySlug($slug);
        }
    }

    public function forgetForModel(Page|Post $model): void
    {
        $slugs = $model->slugs()
            ->pluck('slug')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->forgetMany($slugs);
    }

    public function getCacheKey(string $slug): string
    {
        return 'content_slug:' . trim($slug);
    }
}
