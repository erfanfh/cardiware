<?php

namespace App\Application\Seo\DTOs;

readonly class OpenGraphDataDTO
{
    public function __construct(
        public ?string $type = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $url = null,
        public ?string $siteName = null,
        public ?string $locale = null,
        public ?string $image = null,
        public ?int    $imageWidth = null,
        public ?int    $imageHeight = null,
        public ?string $imageType = null,
        public ?string $publishedTime = null,
        public ?string $modifiedTime = null,
    )
    {
    }

    /**
     * @return array<string, string|int>
     */
    public function toMetaArray(): array
    {
        return array_filter([
            'og:type' => $this->type,
            'og:title' => $this->title,
            'og:description' => $this->description,
            'og:url' => $this->url,
            'og:site_name' => $this->siteName,
            'og:locale' => $this->locale,
            'og:image' => $this->image,
            'og:image:width' => $this->imageWidth,
            'og:image:height' => $this->imageHeight,
            'og:image:type' => $this->imageType,
            'article:published_time' => $this->publishedTime,
            'article:modified_time' => $this->modifiedTime,
        ], fn($value) => !is_null($value) && $value !== '');
    }
}
