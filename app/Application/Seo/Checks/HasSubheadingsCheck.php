<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class HasSubheadingsCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'has_subheadings';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $h2Count = count(array_filter(
            array_map(static fn($value) => trim((string)$value), $input->h2Texts),
            static fn(string $value) => $value !== ''
        ));

        $h3Count = count(array_filter(
            array_map(static fn($value) => trim((string)$value), $input->h3Texts),
            static fn(string $value) => $value !== ''
        ));

        $subheadingCount = $h2Count + $h3Count;
        $wordCount = max(0, (int)$input->wordCount);

        if ($subheadingCount >= 1) {
            return $this->makeResult(
                title: 'زیرعنوان‌ها',
                description: "این متن {$subheadingCount} زیرعنوان دارد. خیلی خوب است.",
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'subheading_count' => $subheadingCount,
                    'h2_count' => $h2Count,
                    'h3_count' => $h3Count,
                    'word_count' => $wordCount,
                ],
                priority: 77,
            );
        }

        if ($wordCount < 300) {
            return $this->makeResult(
                title: 'زیرعنوان‌ها',
                description: 'این متن زیرعنوانی ندارد. برای متن‌های کوتاه خیلی مسئله مهمی نیست، اما استفاده از زیرعنوان می‌تواند ساختار محتوا را بهتر کند.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'subheading_count' => 0,
                    'h2_count' => 0,
                    'h3_count' => 0,
                    'word_count' => $wordCount,
                ],
                priority: 77,
            );
        }

        return $this->makeResult(
            title: 'زیرعنوان‌ها',
            description: 'این متن هیچ زیرعنوان H2 یا H3 ندارد. بهتر است برای خوانایی و ساختار بهتر، از زیرعنوان‌ها استفاده کنید.',
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'subheading_count' => 0,
                'h2_count' => 0,
                'h3_count' => 0,
                'word_count' => $wordCount,
            ],
            priority: 77,
        );
    }
}
