<?php

namespace App\Providers;

use App\Application\Identity\Actions\PermissionCheckerAction;
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
            if (!str_contains($ability, '.'))
                return null;

            [$group, $action] = explode('.', $ability, 2);

            return Cache::tags(['user_access', "user_access:$user->id"])->remember("user_access:$user->id:$group:$action", now()->addHours(config('cache.user_access_lifetime')), function () use ($action, $group, $user) {
                return app(PermissionCheckerAction::class)->execute($group, $action, $user) ?: null;
            });
        });
    }
}
