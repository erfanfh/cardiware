<?php

namespace App\Application\Seo\Actions\Redirect;

use App\Application\Shared\Cache\CacheKey;
use App\Domain\Seo\Models\Redirect;
use Illuminate\Support\Facades\Cache;

class FindMatchingRedirectAction
{
    public function __construct(
        private readonly NormalizeRedirectPathAction $normalizeRedirectPathAction,
    )
    {
    }

    public function execute(string $path): ?Redirect
    {
        $normalizedPath = $this->normalizeRedirectPathAction->execute($path);

        $version = Cache::rememberForever(
            CacheKey::seoRedirectsVersion(),
            fn() => now()->timestamp
        );

        $cacheKey = CacheKey::seoRedirect($version . ':' . $normalizedPath);

        $cached = Cache::rememberForever($cacheKey, function () use ($normalizedPath) {
            $redirect = Redirect::query()
                ->where('is_active', true)
                ->where('format', 'plain')
                ->where('source_path', $normalizedPath)
                ->first();

            return $redirect ?: false;
        });

        return $cached instanceof Redirect ? $cached : null;
    }
}
