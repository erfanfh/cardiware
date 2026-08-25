<?php

namespace App\Application\Seo\DTOs;

use App\Domain\Seo\Enums\OgTypeEnum;

readonly class OpenGraphDataDTO
{
    public function __construct(
        public ?OgTypeEnum $type = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $url = null,
        public ?string $siteName = null,
        public ?string $locale = null,
        public ?string $image = null,
        public ?int $imageWidth = null,
        public ?int $imageHeight = null,
        public ?string $imageType = null,
        public ?string $publishedTime = null,
        public ?string $modifiedTime = null,
        public ?string $video = null,
        public ?string $videoSecureUrl = null,
        public ?string $videoType = null,
        public ?int $videoDuration = null,
        public ?string $videoReleaseDate = null,
    ) {}

    /**
     * @return array<string, string|int>
     */
    public function toMetaArray(): array
    {
        return array_filter([
            'og:type' => $this->type->value,
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
            'og:video' => $this->video,
            'og:video:secure_url' => $this->videoSecureUrl,
            'og:video:type' => $this->videoType,
            'video:duration' => $this->videoDuration,
            'video:release_date' => $this->videoReleaseDate,
        ], fn ($value) => ! is_null($value) && $value !== '');
    }
}
