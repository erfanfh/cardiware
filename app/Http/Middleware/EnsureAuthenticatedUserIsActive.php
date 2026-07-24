<?php

namespace App\Http\Middleware;

use App\Application\Identity\Actions\Auth\EnsureUserCanRemainAuthenticatedAction;
use App\Application\Identity\Actions\Auth\TerminateInactiveUserAuthenticationAction;
use App\Application\Identity\Exceptions\AccountUnavailableException;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAuthenticatedUserIsActive
{
    public function __construct(
        private readonly EnsureUserCanRemainAuthenticatedAction    $ensureUserCanRemainAuthenticatedAction,
        private readonly TerminateInactiveUserAuthenticationAction $terminateInactiveUserAuthenticationAction,
    )
    {
    }

    public function handle(
        Request $request,
        Closure $next,
    ): Response
    {
        $user = $request->user();

        /*
         * کاربر مهمان است و نیازی به بررسی ندارد.
         */
        if (!$user) {
            return $next($request);
        }

        try {
            $this->ensureUserCanRemainAuthenticatedAction->execute(
                $user
            );
        } catch (AccountUnavailableException $exception) {
            $this->terminateInactiveUserAuthenticationAction->execute(
                request: $request,
                user: $user,
            );

            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => $exception->getMessage(),
                ], 403);
            }

            return redirect()
                ->route('auth.index')
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }

        return $next($request);
    }
}
