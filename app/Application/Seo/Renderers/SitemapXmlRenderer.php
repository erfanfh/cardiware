<?php

namespace App\Application\Seo\Renderers;

use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use Carbon\CarbonInterface;

class SitemapXmlRenderer
{
    /**
     * @param array<int, SitemapUrlDTO> $urls
     */
    public function urlset(array $urls): string
    {
        $items = collect($urls)
            ->map(function (SitemapUrlDTO $url) {
                $xml = '    <url>' . PHP_EOL;
                $xml .= '        <loc>' . e($url->url) . '</loc>' . PHP_EOL;

                if ($url->lastmod) {
                    $xml .= '        <lastmod>' . $this->formatDate($url->lastmod) . '</lastmod>' . PHP_EOL;
                }

                foreach ($url->images as $image) {
                    $xml .= '        <image:image>' . PHP_EOL;
                    $xml .= '            <image:loc>' . e($image->url) . '</image:loc>' . PHP_EOL;
                    $xml .= '        </image:image>' . PHP_EOL;
                }

                $xml .= '    </url>';

                return $xml;
            })
            ->implode(PHP_EOL);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
$items
</urlset>

XML;
    }

    /**
     * @param array<int, SitemapIndexItemDTO> $items
     */
    public function index(array $items): string
    {
        $xmlItems = collect($items)
            ->map(function (SitemapIndexItemDTO $item) {
                $xml = '    <sitemap>' . PHP_EOL;
                $xml .= '        <loc>' . e($item->url) . '</loc>' . PHP_EOL;

                if ($item->lastmod) {
                    $xml .= '        <lastmod>' . $this->formatDate($item->lastmod) . '</lastmod>' . PHP_EOL;
                }

                $xml .= '    </sitemap>';

                return $xml;
            })
            ->implode(PHP_EOL);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
$xmlItems
</sitemapindex>

XML;
    }

    private function formatDate(CarbonInterface $date): string
    {
        return $date->toAtomString();
    }
}
