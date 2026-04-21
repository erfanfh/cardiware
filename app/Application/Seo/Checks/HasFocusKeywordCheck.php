<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class HasFocusKeywordCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'has_focus_keyword';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): \App\Application\Seo\DTOs\SeoCheckResultDTO
    {
        if (trim((string)$input->focusKeyword) !== '') {
            return $this->makeResult(
                title: 'کلیدواژه کانونی',
                description: 'کلیدواژه کانونی برای این صفحه مشخص شده است.',
                status: SeoCheckStatusEnum::GOOD,
                priority: 100,
            );
        }

        return $this->makeResult(
            title: 'کلیدواژه کانونی',
            description: 'برای تحلیل دقیق سئو باید کلیدواژه کانونی را مشخص کنید.',
            status: SeoCheckStatusEnum::PROBLEM,
            priority: 100,
        );
    }
}
