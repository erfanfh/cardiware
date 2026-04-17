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
    )
    {
    }

    /**
     * @return array<string, string>
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
        ], fn(?string $value) => filled($value));
    }
}
