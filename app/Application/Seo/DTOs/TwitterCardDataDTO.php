<?php

namespace App\Application\Seo\DTOs;

readonly class TwitterCardDataDTO
{
    public function __construct(
        public ?string $card = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $image = null,
        public ?string $label1 = null,
        public ?string $data1 = null,
        public ?string $label2 = null,
        public ?string $data2 = null,
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
            'twitter:label1' => $this->label1,
            'twitter:data1' => $this->data1,
            'twitter:label2' => $this->label2,
            'twitter:data2' => $this->data2,
        ], fn(?string $value) => filled($value));
    }
}
