<?php

namespace App\Application\Seo\Renderers;

use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\SitemapUrlEncoder;
use Illuminate\Http\Response;

class SitemapXmlRenderer
{
    public function __construct(
        private readonly SitemapUrlEncoder $urlEncoder,
    )
    {
    }

    /**
     * @param array<int, SitemapUrlDTO> $urls
     */
    public function render(array $urls): Response
    {
        $xml = view('front.seo.sitemap.urlset', [
            'urls' => $urls,
            'urlEncoder' => $this->urlEncoder,
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
