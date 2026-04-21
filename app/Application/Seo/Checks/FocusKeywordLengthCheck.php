<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class FocusKeywordLengthCheck extends AbstractSeoCheck
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'focus_keyword_length';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);

        if ($keyword === '') {
            return $this->makeResult(
                title: 'طول عبارت کلیدی',
                description: 'تا وقتی کلیدواژه کانونی تعیین نشده باشد، طول آن قابل بررسی نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 70,
            );
        }

        $count = $this->persianTextNormalizer->wordCount($keyword);

        if ($count >= 1 && $count <= 4) {
            return $this->makeResult(
                title: 'طول عبارت کلیدی',
                description: "عبارت کلیدی شامل {$count} واژه است. خیلی خوبه!",
                status: SeoCheckStatusEnum::GOOD,
                meta: ['word_count' => $count],
                priority: 70,
            );
        }

        if ($count === 5) {
            return $this->makeResult(
                title: 'طول عبارت کلیدی',
                description: 'عبارت کلیدی شامل 5 واژه است. بهتر است کوتاه‌تر شود.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: ['word_count' => $count],
                priority: 70,
            );
        }

        return $this->makeResult(
            title: 'طول عبارت کلیدی',
            description: "عبارت کلیدی شامل {$count} واژه است و بیش از حد طولانی به نظر می‌رسد.",
            status: SeoCheckStatusEnum::PROBLEM,
            meta: ['word_count' => $count],
            priority: 70,
        );
    }
}
