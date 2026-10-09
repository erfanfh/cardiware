<?php

namespace App\Http\Middleware;

use App\Application\Launcher\Exceptions\LauncherAccountSuspendedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureLauncherAccountIsAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->isLauncherSuspended()) {
            $user->tokens()->where('name', 'like', 'MW Launcher%')->delete();
            throw new LauncherAccountSuspendedException($user);
        }

        // Maintenance applies to every authenticated launcher request too, so a launcher
        // that was already open when maintenance started stops as well. Logout stays
        // allowed. Tokens are NOT revoked: when maintenance ends, users carry on.
        if (!$request->routeIs('*auth.logout') && !$request->is('*/auth/logout')) {
            $maintenance = app(\App\Application\Launcher\Support\LauncherMaintenance::class);
            if ($maintenance->blocks($user)) {
                throw new \App\Application\Launcher\Exceptions\LauncherMaintenanceException($maintenance->message());
            }
        }

        return $next($request);
    }
}
