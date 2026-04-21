<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class AvoidH1InContentCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'avoid_h1_in_content';
    }

    public function weight(): int
    {
        return 4;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $h1Count = max(0, (int)$input->h1Count);

        if ($h1Count === 0) {
            return $this->makeResult(
                title: 'استفاده از H1 در متن',
                description: 'در متن نوشته از H1 استفاده نشده است. عالی است.',
                status: SeoCheckStatusEnum::GOOD,
                meta: ['h1_count' => $h1Count],
                priority: 72,
            );
        }

        if ($h1Count === 1) {
            return $this->makeResult(
                title: 'استفاده از H1 در متن',
                description: 'در متن نوشته یک H1 دیده شد. بهتر است H1 فقط برای عنوان اصلی صفحه استفاده شود.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: ['h1_count' => $h1Count],
                priority: 72,
            );
        }

        return $this->makeResult(
            title: 'استفاده از H1 در متن',
            description: "در متن نوشته {$h1Count} تگ H1 دیده شد. بهتر است از H2 و H3 برای زیرعنوان‌ها استفاده کنید.",
            status: SeoCheckStatusEnum::PROBLEM,
            meta: ['h1_count' => $h1Count],
            priority: 72,
        );
    }
}
