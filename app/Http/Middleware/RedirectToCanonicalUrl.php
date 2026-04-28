<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RedirectToCanonicalUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->isSafeMethod($request)) {
            return $next($request);
        }

        $scheme = $request->getScheme();
        $host = $request->getHttpHost();
        $path = $request->getPathInfo();

        if (Str::startsWith($host, 'www.')) {
            $host = Str::after($host, 'www.');
        }

        if ($this->shouldForceHttps()) {
            $scheme = 'https';
        }

        $queryString = $this->normalizedQueryString($request);

        $currentUrl = $request->getSchemeAndHttpHost() . $request->getRequestUri();

        $targetUrl = $scheme . '://' . $host . $path;

        if ($queryString !== '') {
            $targetUrl .= '?' . $queryString;
        }

        if ($currentUrl !== $targetUrl) {
            return redirect()->to($targetUrl, 301);
        }

        return $next($request);
    }

    private function isSafeMethod(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'HEAD'], true);
    }

    private function shouldForceHttps(): bool
    {
        return !app()->environment(['local', 'testing']);
    }

    private function normalizedQueryString(Request $request): string
    {
        $query = $request->query();

        if (empty($query)) {
            return '';
        }

        ksort($query);

        return http_build_query($query);
    }
}
