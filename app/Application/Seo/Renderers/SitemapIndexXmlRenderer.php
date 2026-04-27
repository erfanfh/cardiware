<?php

namespace App\Application\Seo\Renderers;

use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\Support\Sitemap\SitemapUrlEncoder;
use Illuminate\Http\Response;

class SitemapIndexXmlRenderer
{
    public function __construct(
        private readonly SitemapUrlEncoder $urlEncoder,
    )
    {
    }

    /**
     * @param array<int, SitemapIndexItemDTO> $items
     */
    public function render(array $items): Response
    {
        $xml = view('front.seo.sitemap.index', [
            'items' => $items,
            'urlEncoder' => $this->urlEncoder,
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
