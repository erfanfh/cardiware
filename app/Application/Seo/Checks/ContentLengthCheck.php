<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class ContentLengthCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'content_length';
    }

    public function weight(): int
    {
        return 7;
    }

    public function analyze(SeoAnalysisInputDTO $input): \App\Application\Seo\DTOs\SeoCheckResultDTO
    {
        $count = $input->wordCount;

        if ($count >= 300) {
            return $this->makeResult(
                title: 'طول متن',
                description: "متن شامل {$count} واژه است. کار عالی!",
                status: SeoCheckStatusEnum::GOOD,
                meta: ['word_count' => $count],
                priority: 75,
            );
        }

        if ($count >= 200) {
            return $this->makeResult(
                title: 'طول متن',
                description: "متن شامل {$count} واژه است. بد نیست، اما بهتر است کمی کامل‌تر شود.",
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: ['word_count' => $count],
                priority: 75,
            );
        }

        return $this->makeResult(
            title: 'طول متن',
            description: "متن شامل {$count} واژه است و برای سئو کوتاه به نظر می‌رسد.",
            status: SeoCheckStatusEnum::PROBLEM,
            meta: ['word_count' => $count],
            priority: 75,
        );
    }
}
