<?php

namespace App\Application\Seo\Support\Sitemap;

class SitemapUrlEncoder
{
    /**
     * Encode URL path segments safely without breaking the scheme and host.
     */
    public function encode(string $url): string
    {
        $parts = parse_url($url);

        if (!isset($parts['scheme'], $parts['host'])) {
            return e($url);
        }

        $path = $parts['path'] ?? '/';

        $encodedPath = collect(explode('/', $path))
            ->map(fn(string $segment) => rawurlencode(rawurldecode($segment)))
            ->implode('/');

        $result = $parts['scheme'] . '://' . $parts['host'];

        if (isset($parts['port'])) {
            $result .= ':' . $parts['port'];
        }

        $result .= $encodedPath;

        if (isset($parts['query'])) {
            $result .= '?' . $parts['query'];
        }

        return e($result);
    }
}
