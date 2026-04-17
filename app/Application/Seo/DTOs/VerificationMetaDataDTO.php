<?php

namespace App\Application\Seo\DTOs;

readonly class VerificationMetaDataDTO
{
    public function __construct(
        public ?string $googleSiteVerification = null,
        public ?string $bingSiteVerification = null,
    )
    {
    }

    /**
     * @return array<string, string>
     */
    public function toMetaArray(): array
    {
        return array_filter([
            'google-site-verification' => $this->googleSiteVerification,
            'msvalidate.01' => $this->bingSiteVerification,
        ], fn(?string $value) => filled($value));
    }
}
