<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class HasMetaDescriptionCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'has_meta_description';
    }

    public function weight(): int
    {
        return 7;
    }

    public function analyze(SeoAnalysisInputDTO $input): \App\Application\Seo\DTOs\SeoCheckResultDTO
    {
        if (trim((string)$input->resolvedSeoDescription) !== '') {
            return $this->makeResult(
                title: 'توضیح متا',
                description: 'برای این صفحه توضیح متا وجود دارد. خیلی خوب است.',
                status: SeoCheckStatusEnum::GOOD,
                priority: 88,
            );
        }

        return $this->makeResult(
            title: 'توضیح متا',
            description: 'برای این صفحه توضیح متا تنظیم نشده است.',
            status: SeoCheckStatusEnum::PROBLEM,
            priority: 88,
        );
    }
}
