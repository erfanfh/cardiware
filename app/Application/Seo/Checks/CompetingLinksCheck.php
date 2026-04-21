<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class CompetingLinksCheck extends AbstractSeoCheck
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'competing_links';
    }

    public function weight(): int
    {
        return 6;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);
        $anchorTexts = array_values(array_filter(
            array_map(static fn($value) => trim((string)$value), $input->internalLinkAnchorTexts),
            static fn(string $value) => $value !== ''
        ));

        if ($keyword === '') {
            return $this->makeResult(
                title: 'پیوندهای رقیب',
                description: 'تا وقتی کلیدواژه کانونی تعیین نشده باشد، بررسی پیوندهای رقیب ممکن نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 69,
            );
        }

        if ($anchorTexts === []) {
            return $this->makeResult(
                title: 'پیوندهای رقیب',
                description: 'هیچ متن لنگر داخلی‌ای پیدا نشد که با کلیدواژه کانونی رقابت کند. عالی است!',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'internal_anchor_count' => 0,
                    'competing_anchor_count' => 0,
                ],
                priority: 69,
            );
        }

        $competingAnchorCount = 0;

        foreach ($anchorTexts as $anchorText) {
            if ($this->persianTextNormalizer->contains($anchorText, $keyword)) {
                $competingAnchorCount++;
            }
        }

        if ($competingAnchorCount === 0) {
            return $this->makeResult(
                title: 'پیوندهای رقیب',
                description: 'هیچ پیوند داخلی‌ای وجود ندارد که از کلیدواژه کانونی به‌عنوان متن لنگر استفاده کند. عالی است!',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'internal_anchor_count' => count($anchorTexts),
                    'competing_anchor_count' => 0,
                ],
                priority: 69,
            );
        }

        if ($competingAnchorCount === 1) {
            return $this->makeResult(
                title: 'پیوندهای رقیب',
                description: 'یک پیوند داخلی با متن لنگر نزدیک به کلیدواژه کانونی پیدا شد. بهتر است بررسی کنید که با این صفحه رقابت نکند.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'internal_anchor_count' => count($anchorTexts),
                    'competing_anchor_count' => $competingAnchorCount,
                ],
                priority: 69,
            );
        }

        return $this->makeResult(
            title: 'پیوندهای رقیب',
            description: "تعداد {$competingAnchorCount} پیوند داخلی با متن لنگر نزدیک به کلیدواژه کانونی پیدا شد. این موضوع می‌تواند باعث رقابت داخلی شود.",
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'internal_anchor_count' => count($anchorTexts),
                'competing_anchor_count' => $competingAnchorCount,
            ],
            priority: 69,
        );
    }
}
