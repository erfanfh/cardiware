<?php

namespace App\Application\Seo\Actions;

use App\Application\Seo\DTOs\SeoPaginationLinksDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ResolveSeoPaginationLinksAction
{
    public function execute(
        LengthAwarePaginator $paginator,
        string               $baseUrl,
        string               $mode = 'path',
    ): SeoPaginationLinksDTO
    {
        $currentPage = $paginator->currentPage();

        return new SeoPaginationLinksDTO(
            prev: $currentPage > 1
                ? $this->buildUrl($baseUrl, $currentPage - 1, $mode)
                : null,

            next: $paginator->hasMorePages()
                ? $this->buildUrl($baseUrl, $currentPage + 1, $mode)
                : null,
        );
    }

    private function buildUrl(string $baseUrl, int $page, string $mode): string
    {
        return match ($mode) {
            'query' => $this->buildQueryUrl($baseUrl, $page),
            default => $this->buildPathUrl($baseUrl, $page),
        };
    }

    private function buildPathUrl(string $baseUrl, int $page): string
    {
        $baseUrl = rtrim($baseUrl, '/') . '/';

        if ($page <= 1) {
            return $baseUrl;
        }

        return $baseUrl . 'page/' . $page . '/';
    }

    private function buildQueryUrl(string $baseUrl, int $page): string
    {
        if ($page <= 1) {
            return $this->removePageQuery($baseUrl);
        }

        return $this->replaceQueryParameter($baseUrl, 'page', (string)$page);
    }

    private function removePageQuery(string $url): string
    {
        $parts = parse_url($url);

        parse_str($parts['query'] ?? '', $query);

        unset($query['page']);

        return $this->buildUrlFromParts($parts, $query);
    }

    private function replaceQueryParameter(string $url, string $key, string $value): string
    {
        $parts = parse_url($url);

        parse_str($parts['query'] ?? '', $query);

        $query[$key] = $value;

        return $this->buildUrlFromParts($parts, $query);
    }

    /**
     * @param array<string, mixed> $parts
     * @param array<string, mixed> $query
     */
    private function buildUrlFromParts(array $parts, array $query): string
    {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';

        $url = $scheme . $host . $port . $path;

        if ($query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        return $url;
    }
}
