<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class SlugFormatCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'slug_format';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $slug = trim((string)$input->slug);

        if ($slug === '') {
            return $this->makeResult(
                title: 'فرمت نامک',
                description: 'تا وقتی نامک مشخص نباشد، فرمت آن قابل بررسی نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 62,
            );
        }

        $issues = [];

        if (str_contains($slug, ' ')) {
            $issues[] = 'space';
        }

        if (str_starts_with($slug, '-') || str_ends_with($slug, '-')) {
            $issues[] = 'edge-dash';
        }

        if (str_contains($slug, '--')) {
            $issues[] = 'double-dash';
        }

        if (str_contains($slug, '__')) {
            $issues[] = 'double-underscore';
        }

        if ($issues === []) {
            return $this->makeResult(
                title: 'فرمت نامک',
                description: 'فرمت نامک تمیز و مناسب است. خیلی خوب است.',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'issues' => [],
                ],
                priority: 62,
            );
        }

        if (count($issues) === 1) {
            return $this->makeResult(
                title: 'فرمت نامک',
                description: 'فرمت نامک یک ایراد جزئی دارد. بهتر است آن را تمیزتر و استانداردتر کنید.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'issues' => $issues,
                ],
                priority: 62,
            );
        }

        return $this->makeResult(
            title: 'فرمت نامک',
            description: 'فرمت نامک چند ایراد دارد. بهتر است آن را بازبینی و استانداردتر کنید.',
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'issues' => $issues,
            ],
            priority: 62,
        );
    }
}
