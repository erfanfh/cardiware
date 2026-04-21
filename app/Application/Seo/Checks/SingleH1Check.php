<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class SingleH1Check extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'single_h1';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): \App\Application\Seo\DTOs\SeoCheckResultDTO
    {
        if ($input->h1Count === 1) {
            return $this->makeResult(
                title: 'عنوان یکتا',
                description: 'شما بیش از یک عنوان H1 ندارید — عالی است!',
                status: SeoCheckStatusEnum::GOOD,
                meta: ['h1_count' => $input->h1Count],
                priority: 80,
            );
        }

        if ($input->h1Count === 0) {
            return $this->makeResult(
                title: 'عنوان یکتا',
                description: 'هیچ H1 در محتوای صفحه پیدا نشد. بهتر است ساختار عنوان بازبینی شود.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: ['h1_count' => $input->h1Count],
                priority: 80,
            );
        }

        return $this->makeResult(
            title: 'عنوان یکتا',
            description: "این صفحه {$input->h1Count} عنوان H1 دارد. بهتر است فقط یک H1 داشته باشید.",
            status: SeoCheckStatusEnum::PROBLEM,
            meta: ['h1_count' => $input->h1Count],
            priority: 80,
        );
    }
}
