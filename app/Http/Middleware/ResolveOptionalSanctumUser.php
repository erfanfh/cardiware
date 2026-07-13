<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveOptionalSanctumUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() === null) {
            return $next($request);
        }

        $user = Auth::guard('sanctum')->user();

        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $request->setUserResolver(
            static fn() => $user
        );

        return $next($request);
    }
}
