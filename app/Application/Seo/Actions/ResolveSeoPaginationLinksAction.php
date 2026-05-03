<?php

namespace App\Application\Seo\Actions;

use App\Application\Seo\DTOs\SeoPaginationLinksDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ResolveSeoPaginationLinksAction
{
    public function execute(LengthAwarePaginator $paginator, string $baseUrl): SeoPaginationLinksDTO
    {
        $currentPage = $paginator->currentPage();

        return new SeoPaginationLinksDTO(
            prev: $currentPage > 1
                ? $this->buildUrl($baseUrl, $currentPage - 1)
                : null,

            next: $paginator->hasMorePages()
                ? $this->buildUrl($baseUrl, $currentPage + 1)
                : null,
        );
    }

    private function buildUrl(string $baseUrl, int $page): string
    {
        $baseUrl = rtrim($baseUrl, '/') . '/';

        if ($page <= 1) {
            return $baseUrl;
        }

        return $baseUrl . 'page/' . $page . '/';
    }
}
