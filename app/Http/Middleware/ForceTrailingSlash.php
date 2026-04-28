<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceTrailingSlash
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->isSafeMethod($request)) {
            return $next($request);
        }

        $path = $request->getPathInfo();

        if ($path === '/' || str_ends_with($path, '/')) {
            return $next($request);
        }

        $targetUrl = $request->getSchemeAndHttpHost()
            . $path
            . '/';

        if ($request->getQueryString()) {
            $targetUrl .= '?' . $request->getQueryString();
        }

        return redirect()->to($targetUrl, 301);
    }

    private function isSafeMethod(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'HEAD'], true);
    }
}
