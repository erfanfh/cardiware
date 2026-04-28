<?php

namespace App\Application\Seo\Support;

use Illuminate\Support\Str;

class CanonicalUrlGenerator
{
    public function generate(string $url, bool $forceTrailingSlash = true): string
    {
        $parts = parse_url($url);

        $scheme = $parts['scheme'] ?? request()->getScheme();
        $host = $parts['host'] ?? request()->getHost();
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        if (Str::startsWith($host, 'www.')) {
            $host = Str::after($host, 'www.');
        }

        if (!app()->environment(['local', 'staging'])) {
            $scheme = 'https';
        }

        if ($forceTrailingSlash && $path !== '/' && !str_ends_with($path, '/')) {
            $path .= '/';
        }

        return $scheme . '://' . $host . $port . $path . $query;
    }
}
