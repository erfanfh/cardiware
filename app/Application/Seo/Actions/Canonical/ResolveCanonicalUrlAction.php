<?php

namespace App\Application\Seo\Actions\Canonical;

final class ResolveCanonicalUrlAction
{
    public function handle(?string $customCanonicalUrl = null, ?string $fallbackUrl = null): string
    {
        $url = $this->firstFilled($customCanonicalUrl, $fallbackUrl, url('/'));

        return $this->normalize($url);
    }

    protected function firstFilled(?string ...$values): string
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return (string)$value;
            }
        }

        return url('/');
    }

    protected function normalize(string $url): string
    {
        $url = trim($url);

        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $scheme = $parts['scheme'] ?? parse_url(url('/'), PHP_URL_SCHEME);
        $host = $parts['host'] ?? parse_url(url('/'), PHP_URL_HOST);
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '/';

        $normalizedPath = $path === '/' ? '/' : rtrim($path, '/');

        return "{$scheme}://{$host}{$port}{$normalizedPath}";
    }
}
