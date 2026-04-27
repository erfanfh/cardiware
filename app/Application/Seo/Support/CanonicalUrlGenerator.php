<?php

namespace App\Application\Seo\Support;

use Illuminate\Support\Str;

class CanonicalUrlGenerator
{
    public function generate(string $url): string
    {
        $parts = parse_url($url);

        $scheme = $parts['scheme'] ?? request()->getScheme();
        $host = $parts['host'] ?? request()->getHttpHost();
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        if (Str::startsWith($host, 'www.')) {
            $host = Str::after($host, 'www.');
        }

        if (!app()->environment(['local', 'testing'])) {
            $scheme = 'https';
        }

        $path = preg_replace('#/+#', '/', $path) ?: '/';

        if ($path !== '/' && !str_ends_with($path, '/')) {
            $path .= '/';
        }

        return $scheme . '://' . $host . $port . $path . $query;
    }
}
