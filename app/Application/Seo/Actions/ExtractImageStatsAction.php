<?php

namespace App\Application\Seo\Actions;

final class ExtractImageStatsAction
{
    /**
     * @return array{
     *     image_count: int,
     *     image_alt_texts: array<int, string>
     * }
     */
    public function execute(?string $html): array
    {
        if (blank($html)) {
            return [
                'image_count' => 0,
                'image_alt_texts' => [],
            ];
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

        $images = $dom->getElementsByTagName('img');

        $imageCount = 0;
        $imageAltTexts = [];

        foreach ($images as $image) {
            $src = trim((string)$image->getAttribute('src'));

            if ($src === '') {
                continue;
            }

            $imageCount++;

            $alt = trim((string)$image->getAttribute('alt'));

            if ($alt !== '') {
                $imageAltTexts[] = $alt;
            }
        }

        return [
            'image_count' => $imageCount,
            'image_alt_texts' => $imageAltTexts,
        ];
    }
}
