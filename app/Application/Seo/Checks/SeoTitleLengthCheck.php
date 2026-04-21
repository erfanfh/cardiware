<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class SeoTitleLengthCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'seo_title_length';
    }

    public function weight(): int
    {
        return 8;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $seoTitle = trim((string)$input->resolvedSeoTitle);

        if ($seoTitle === '') {
            return $this->makeResult(
                title: 'طول عنوان سئو',
                description: 'تا وقتی عنوان سئو در دسترس نباشد، طول آن قابل بررسی نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 90,
            );
        }

        $length = mb_strlen($seoTitle);

        if ($length < 30) {
            return $this->makeResult(
                title: 'طول عنوان سئو',
                description: "عنوان سئو {$length} کاراکتر دارد و کمی کوتاه به نظر می‌رسد. بهتر است توصیفی‌تر باشد.",
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: ['length' => $length],
                priority: 90,
            );
        }

        if ($length <= 60) {
            return $this->makeResult(
                title: 'طول عنوان سئو',
                description: "عنوان سئو {$length} کاراکتر دارد. خیلی عالی است!",
                status: SeoCheckStatusEnum::GOOD,
                meta: ['length' => $length],
                priority: 90,
            );
        }

        return $this->makeResult(
            title: 'طول عنوان سئو',
            description: "عنوان سئو {$length} کاراکتر دارد و احتمالاً برای نتایج جستجو بیش از حد بلند است. بهتر است کوتاه‌تر شود.",
            status: SeoCheckStatusEnum::PROBLEM,
            meta: ['length' => $length],
            priority: 90,
        );
    }
}
