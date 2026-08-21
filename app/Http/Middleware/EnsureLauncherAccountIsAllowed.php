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

        return $next($request);
    }
}
