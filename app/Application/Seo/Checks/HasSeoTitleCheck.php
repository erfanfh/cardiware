<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class HasSeoTitleCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'has_seo_title';
    }

    public function weight(): int
    {
        return 7;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        if (trim((string)$input->resolvedSeoTitle) !== '') {
            return $this->makeResult(
                title: 'عنوان سئو',
                description: 'برای این صفحه عنوان سئو در دسترس است. خیلی عالی!',
                status: SeoCheckStatusEnum::GOOD,
                priority: 95,
            );
        }

        return $this->makeResult(
            title: 'عنوان سئو',
            description: 'برای این صفحه هیچ عنوان سئوی قابل استفاده‌ای پیدا نشد.',
            status: SeoCheckStatusEnum::PROBLEM,
            priority: 95,
        );
    }
}
