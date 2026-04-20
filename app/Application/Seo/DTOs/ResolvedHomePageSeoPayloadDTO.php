<?php

namespace App\Application\Seo\DTOs;

final class ResolvedHomePageSeoPayloadDTO
{
    public function __construct(
        public readonly string  $canonicalUrl,
        public readonly ?string $robots = null,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $ogTitle = null,
        public readonly ?string $ogDescription = null,
    )
    {
    }
}
