<?php

namespace App\Http\Middleware;

use App\Application\Seo\Actions\Redirect\FindMatchingRedirectAction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleSeoRedirectsMiddleware
{
    public function __construct(
        private readonly FindMatchingRedirectAction $findMatchingRedirectAction,
    )
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            return $next($request);
        }

        $redirect = $this->findMatchingRedirectAction->execute($request->getPathInfo());

        if (!$redirect) {
            return $next($request);
        }

        if ($redirect->status_code->isGone()) {
            abort(410);
        }

        return redirect()->to(
            $redirect->target_url,
            $redirect->status_code->value
        );
    }
}
