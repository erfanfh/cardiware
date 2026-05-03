<?php

namespace App\Infrastructure\Support;

use App\Application\Seo\Services\PersianTextNormalizer;

class ReadingTimeEstimator
{
    public function __construct(
        private readonly PersianTextNormalizer $normalizer,
    )
    {
    }

    public function estimate(?string $content, int $wordsPerMinute = 180): int
    {
        $wordCount = $this->normalizer->wordCount((string)$content);

        return max(1, (int)ceil($wordCount / $wordsPerMinute));
    }
}
