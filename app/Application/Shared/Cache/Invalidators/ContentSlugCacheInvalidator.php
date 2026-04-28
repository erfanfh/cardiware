<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheKey;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use Illuminate\Support\Facades\Cache;

class ContentSlugCacheInvalidator
{
    public function forgetBySlug(string $slug): void
    {
        $slug = trim($slug);

        if ($slug === '') {
            return;
        }

        Cache::forget(CacheKey::contentSlug($slug));
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
}
