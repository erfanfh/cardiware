<?php

namespace App\Application\Slug\DTOs;

final class SlugCheckResultDTO
{
    public function __construct(
        public readonly bool    $available,
        public readonly string  $slug,
        public readonly string  $normalizedSlug,
        public readonly ?string $suggestedSlug = null,
        public readonly ?string $message = null,
    )
    {
    }

    /**
     * Convert the DTO into an array structure suitable for JSON responses.
     *
     * @return array<string, string|bool|null>
     */
    public function toArray(): array
    {
        return [
            'available' => $this->available,
            'slug' => $this->slug,
            'normalized_slug' => $this->normalizedSlug,
            'suggested_slug' => $this->suggestedSlug,
            'message' => $this->message,
        ];
    }
}
