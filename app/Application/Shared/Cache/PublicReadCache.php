<?php

namespace App\Application\Shared\Cache;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Cache public query results without storing request or user state. */
final class PublicReadCache
{
    /**
     * @param  list<class-string<Model>>  $models
     * @param  array<class-string<Model>, list<string>>  $transitions
     */
    public function remember(
        string $resource,
        array $parameters,
        array $models,
        Closure $resolve,
        array $transitions = [],
        int $seconds = 600,
    ): mixed {
        // Preview/admin queries can bypass public scopes and must never seed this cache.
        if (request()->routeIs('admin.*', 'preview.*')) {
            return $resolve();
        }

        $cache = Cache::tags([
            CacheTag::PUBLIC_READS,
            ...array_map(CacheTag::publicModel(...), $models),
        ]);
        $key = CacheKey::publicRead($resource, $parameters);
        $cached = $cache->get($key);

        if (is_array($cached) && array_key_exists('value', $cached)) {
            return $cached['value'];
        }

        $expiresAt = now()->addSeconds($seconds);
        foreach ($transitions as $model => $columns) {
            $expiresAt = $expiresAt->min($this->nextTransition($model, $columns, $seconds));
        }

        $value = $resolve();
        $cache->put($key, ['value' => $value], $expiresAt);

        return $value;
    }

    public function paginate(
        string $resource,
        array $parameters,
        array $models,
        Closure $resolve,
        array $transitions = [],
        int $seconds = 600,
    ): LengthAwarePaginator {
        $paginator = clone $this->remember($resource, $parameters, $models, $resolve, $transitions, $seconds);

        return $paginator->withPath(request()->url());
    }

    private function nextTransition(string $model, array $columns, int $seconds): Carbon
    {
        $cache = Cache::tags([CacheTag::PUBLIC_READS, CacheTag::publicModel($model)]);
        $key = CacheKey::publicRead('next-transition', [$model, $columns, $seconds]);

        if ($expiresAt = $cache->get($key)) {
            return Carbon::parse($expiresAt);
        }

        $expiresAt = now()->addSeconds($seconds);
        foreach ($columns as $column) {
            $next = $model::withoutGlobalScopes()->where($column, '>', now())->min($column);
            if ($next) {
                $expiresAt = $expiresAt->min(Carbon::parse($next));
            }
        }

        $cache->put($key, $expiresAt->toDateTimeString(), $expiresAt);

        return $expiresAt;
    }
}
