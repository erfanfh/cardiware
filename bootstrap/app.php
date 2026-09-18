<?php

use App\Application\Commerce\Exceptions\LauncherManifestChangedException;
use App\Application\Commerce\Exceptions\PurchaseAccessDownloadSessionExpiredException;
use App\Application\Commerce\Exceptions\PurchaseAccessFileUnavailableException;
use App\Application\Commerce\Exceptions\PurchaseAccessIsNotUsableException;
use App\Application\Launcher\Exceptions\DeviceRevokedException;
use App\Http\Middleware\EnsureAuthenticatedUserIsActive;
use App\Http\Middleware\EnsureLauncherAccountIsAllowed;
use App\Http\Middleware\EnsureLauncherDeviceIsActive;
use App\Http\Middleware\ResolveOptionalSanctumUser;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [],
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
        then: function () {
            require base_path('routes/admin.php');
            require base_path('routes/preview.php');
            require base_path('routes/front.php');
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'user.active' => EnsureAuthenticatedUserIsActive::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'auth.optional.sanctum' => ResolveOptionalSanctumUser::class,
            'launcher.device' => EnsureLauncherDeviceIsActive::class,
            'launcher.account' => EnsureLauncherAccountIsAllowed::class,
        ]);

        $middleware->trustProxies(
            at: ['127.0.0.1', '::1'],
            headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->appendToGroup(
            'web',
            EnsureAuthenticatedUserIsActive::class
        );
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->exec('nice -n 10 ionice -c2 -n7 php artisan sitemap:regenerate-if-needed')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command('backups:dispatch-due')
            ->everyFifteenMinutes()
            ->withoutOverlapping(30)
            ->onOneServer();

        $schedule->command('backups:cleanup')
            ->dailyAt('04:10')
            ->withoutOverlapping(180)
            ->onOneServer();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Expected launcher control-flow responses, not faults: each one used to be written
        // to laravel.log as production.ERROR with a ~50-frame stack trace, thousands of times
        // an hour during a release. The client handles all of them (refreshes the session,
        // restarts the file, shows the purchase page).
        $exceptions->dontReport([
            PurchaseAccessDownloadSessionExpiredException::class,
            LauncherManifestChangedException::class,
            PurchaseAccessIsNotUsableException::class,
            PurchaseAccessFileUnavailableException::class,
            DeviceRevokedException::class,
        ]);

        $exceptions->shouldRenderJsonWhen(
            function (Request $request, Throwable $exception): bool {
                return $request->is('api/*')
                    || $request->expectsJson();
            }
        );
    })
    ->create();
