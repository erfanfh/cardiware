<?php

namespace App\Application\Seo\Renderers;

use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use Carbon\CarbonInterface;

class SitemapXmlRenderer
{
    /**
     * @param  array<int, SitemapUrlDTO>  $urls
     */
    public function urlset(array $urls): string
    {
        $hasVideos = collect($urls)->contains(static fn (SitemapUrlDTO $url) => $url->hasVideos());
        $items = collect($urls)
            ->map(function (SitemapUrlDTO $url) {
                $xml = '    <url>'.PHP_EOL;
                $xml .= '        <loc>'.e($url->url).'</loc>'.PHP_EOL;

                if ($url->lastmod) {
                    $xml .= '        <lastmod>'.$this->formatDate($url->lastmod).'</lastmod>'.PHP_EOL;
                }

                foreach ($url->images as $image) {
                    $xml .= '        <image:image>'.PHP_EOL;
                    $xml .= '            <image:loc>'.e($image->url).'</image:loc>'.PHP_EOL;
                    $xml .= '        </image:image>'.PHP_EOL;
                }

                foreach ($url->videos as $video) {
                    $xml .= '        <video:video>'.PHP_EOL;
                    $xml .= '            <video:thumbnail_loc>'.e($video->thumbnailUrl).'</video:thumbnail_loc>'.PHP_EOL;
                    $xml .= '            <video:title>'.e($video->title).'</video:title>'.PHP_EOL;
                    $xml .= '            <video:description>'.e($video->description).'</video:description>'.PHP_EOL;

                    if ($video->contentUrl) {
                        $xml .= '            <video:content_loc>'.e($video->contentUrl).'</video:content_loc>'.PHP_EOL;
                    } elseif ($video->playerUrl) {
                        $xml .= '            <video:player_loc>'.e($video->playerUrl).'</video:player_loc>'.PHP_EOL;
                    }

                    if ($video->duration) {
                        $xml .= '            <video:duration>'.$video->duration.'</video:duration>'.PHP_EOL;
                    }

                    if ($video->publicationDate) {
                        $xml .= '            <video:publication_date>'.$this->formatDate($video->publicationDate).'</video:publication_date>'.PHP_EOL;
                    }

                    if (! is_null($video->viewCount)) {
                        $xml .= '            <video:view_count>'.$video->viewCount.'</video:view_count>'.PHP_EOL;
                    }

                    $xml .= '        </video:video>'.PHP_EOL;
                }

                $xml .= '    </url>';

                return $xml;
            })
            ->implode(PHP_EOL);

        $videoNamespace = $hasVideos
            ? PHP_EOL.'    xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"'
            : '';

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"$videoNamespace>
$items
</urlset>

XML;
    }

    /**
     * @param  array<int, SitemapIndexItemDTO>  $items
     */
    public function index(array $items): string
    {
        $xmlItems = collect($items)
            ->map(function (SitemapIndexItemDTO $item) {
                $xml = '    <sitemap>'.PHP_EOL;
                $xml .= '        <loc>'.e($item->url).'</loc>'.PHP_EOL;

                if ($item->lastmod) {
                    $xml .= '        <lastmod>'.$this->formatDate($item->lastmod).'</lastmod>'.PHP_EOL;
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
        return $date->copy()->utc()->toAtomString();
    }
}
