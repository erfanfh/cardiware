<?php

namespace App\Application\Seo\DTOs;

readonly class TwitterCardDataDTO
{
    public function __construct(
        public ?string $card = null,
        public ?string $title = null,
        public ?string $description = null,
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
            'twitter:card' => $this->card,
            'twitter:title' => $this->title,
            'twitter:description' => $this->description,
            'twitter:image' => $this->image,
        ], fn(?string $value) => filled($value));
    }
}
