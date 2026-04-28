<?php

namespace App\Providers;

use App\Application\Identity\Actions\PermissionCheckerAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PermissionCheckerAction::class, function () {
            return new PermissionCheckerAction();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            if (!str_contains($ability, '.')) {
                return null;
            }

            [$group, $action] = explode('.', $ability, 2);

            return Cache::tags([
                CacheTag::USER_ACCESS,
                CacheTag::userAccess($user->id),
            ])->remember(
                CacheKey::userAccessAbility($user->id, $group, $action),
                CacheTtl::daily(),
                fn() => app(PermissionCheckerAction::class)->execute($group, $action, $user) ?: null
            );
        });
    }
}
