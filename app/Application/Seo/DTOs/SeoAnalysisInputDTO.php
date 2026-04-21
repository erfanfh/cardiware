<?php

namespace App\Application\Seo\DTOs;

final readonly class SeoAnalysisInputDTO
{
    /**
     * @param array<int, string> $h1Texts
     * @param array<int, string> $h2Texts
     * @param array<int, string> $h3Texts
     */
    public function __construct(
        public string  $title,
        public string  $slug,
        public string  $htmlContent,
        public string  $plainTextContent,
        public int     $wordCount,
        public ?string $focusKeyword,
        public ?string $resolvedSeoTitle,
        public ?string $resolvedSeoDescription,
        public int     $h1Count,
        public array   $h1Texts,
        public array   $h2Texts,
        public array   $h3Texts,
    )
    {
    }
}
