<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
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

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        if (!blank($input->title)) {
            return $this->makeResult(
                title: 'وجود عنوان صفحه',
                description: 'صفحه شما عنوان دارد. عالی است.',
                status: SeoCheckStatusEnum::GOOD,
                priority: 90,
            );
        }

        return $this->makeResult(
            title: 'وجود عنوان صفحه',
            description: 'این صفحه عنوان ندارد. یک عنوان مناسب و مرتبط با محتوا اضافه کنید.',
            status: SeoCheckStatusEnum::PROBLEM,
            priority: 90,
        );
    }
}
