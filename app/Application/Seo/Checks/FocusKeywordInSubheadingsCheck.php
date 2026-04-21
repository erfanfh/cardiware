<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class FocusKeywordInSubheadingsCheck extends AbstractSeoCheck
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'focus_keyword_in_subheadings';
    }

    public function weight(): int
    {
        return 7;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);
        $subheadings = array_merge($input->h2Texts, $input->h3Texts);

        if ($keyword === '') {
            return $this->makeResult(
                title: 'کلیدواژه در زیرعنوان‌ها',
                description: 'تا وقتی کلیدواژه کانونی تعیین نشده باشد، بررسی زیرعنوان‌ها ممکن نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 78,
            );
        }

        if ($subheadings === []) {
            return $this->makeResult(
                title: 'کلیدواژه در زیرعنوان‌ها',
                description: 'هیچ زیرعنوان H2 یا H3 برای بررسی پیدا نشد.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'subheading_count' => 0,
                    'matched_count' => 0,
                    'matched_ratio' => 0,
                ],
                priority: 78,
            );
        }

        $matchedCount = 0;
        $totalSubheadings = 0;

        foreach ($subheadings as $subheading) {
            $subheading = trim((string)$subheading);

            if ($subheading === '') {
                continue;
            }

            $totalSubheadings++;

            if ($this->persianTextNormalizer->contains($subheading, $keyword)) {
                $matchedCount++;
            }
        }

        if ($totalSubheadings === 0) {
            return $this->makeResult(
                title: 'کلیدواژه در زیرعنوان‌ها',
                description: 'هیچ زیرعنوان معناداری برای بررسی پیدا نشد.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'subheading_count' => 0,
                    'matched_count' => 0,
                    'matched_ratio' => 0,
                ],
                priority: 78,
            );
        }

        $matchedRatio = $matchedCount / $totalSubheadings;

        if ($matchedRatio >= 0.3) {
            return $this->makeResult(
                title: 'کلیدواژه در زیرعنوان‌ها',
                description: 'زیرعنوان‌های H2 و H3 به‌خوبی موضوع متن را بازتاب می‌دهند. آفرین!',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'subheading_count' => $totalSubheadings,
                    'matched_count' => $matchedCount,
                    'matched_ratio' => round($matchedRatio, 2),
                ],
                priority: 78,
            );
        }

        if ($matchedCount > 0) {
            return $this->makeResult(
                title: 'کلیدواژه در زیرعنوان‌ها',
                description: 'کلیدواژه کانونی در بعضی از زیرعنوان‌ها دیده می‌شود، اما بهتر است در زیرعنوان‌های بیشتری استفاده شود.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'subheading_count' => $totalSubheadings,
                    'matched_count' => $matchedCount,
                    'matched_ratio' => round($matchedRatio, 2),
                ],
                priority: 78,
            );
        }

        return $this->makeResult(
            title: 'کلیدواژه در زیرعنوان‌ها',
            description: 'از کلیدواژه کانونی در زیرعنوان‌های H2 و H3 بیشتر استفاده کنید.',
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'subheading_count' => $totalSubheadings,
                'matched_count' => $matchedCount,
                'matched_ratio' => 0,
            ],
            priority: 78,
        );
    }
}
