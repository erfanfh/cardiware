<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class HasTitleCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'has_title';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): \App\Application\Seo\DTOs\SeoCheckResultDTO
    {
        if (trim($input->title) !== '') {
            return $this->makeResult(
                title: 'Title',
                description: 'صفحه شما عنوان دارد. عالی است.',
                status: SeoCheckStatusEnum::GOOD,
                priority: 90,
            );
        }

        return $this->makeResult(
            title: 'Title',
            description: 'این صفحه عنوان ندارد. باید برای آن عنوان مشخص شود.',
            status: SeoCheckStatusEnum::PROBLEM,
            priority: 90,
        );
    }
}
