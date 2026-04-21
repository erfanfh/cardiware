<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class FocusKeywordInImageAltCheck extends AbstractSeoCheck
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'focus_keyword_in_image_alt';
    }

    public function weight(): int
    {
        return 6;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);
        $imageCount = max(0, (int)$input->imageCount);
        $altTexts = array_values(array_filter(
            array_map(static fn($value) => trim((string)$value), $input->imageAltTexts),
            static fn(string $value) => $value !== ''
        ));

        if ($keyword === '') {
            return $this->makeResult(
                title: 'عبارت کلیدی در ویژگی alt تصاویر',
                description: 'تا وقتی کلیدواژه کانونی تعیین نشده باشد، بررسی alt تصاویر ممکن نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 67,
            );
        }

        if ($imageCount === 0) {
            return $this->makeResult(
                title: 'عبارت کلیدی در ویژگی alt تصاویر',
                description: 'وقتی تصویری در محتوا وجود نداشته باشد، بررسی alt تصاویر ممکن نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'image_count' => $imageCount,
                    'alt_count' => count($altTexts),
                ],
                priority: 67,
            );
        }

        if ($altTexts === []) {
            return $this->makeResult(
                title: 'عبارت کلیدی در ویژگی alt تصاویر',
                description: 'برای تصاویر این محتوا متن alt معناداری پیدا نشد. بهتر است برای تصاویر alt مناسب بنویسید.',
                status: SeoCheckStatusEnum::PROBLEM,
                meta: [
                    'image_count' => $imageCount,
                    'alt_count' => 0,
                    'matched_alt_count' => 0,
                ],
                priority: 67,
            );
        }

        $matchedAltCount = 0;

        foreach ($altTexts as $altText) {
            if ($this->persianTextNormalizer->contains($altText, $keyword)) {
                $matchedAltCount++;
            }
        }

        if ($matchedAltCount >= 1) {
            return $this->makeResult(
                title: 'عبارت کلیدی در ویژگی alt تصاویر',
                description: 'آفرین! کلیدواژه کانونی در ویژگی alt حداقل یکی از تصاویر دیده می‌شود.',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'image_count' => $imageCount,
                    'alt_count' => count($altTexts),
                    'matched_alt_count' => $matchedAltCount,
                ],
                priority: 67,
            );
        }

        return $this->makeResult(
            title: 'عبارت کلیدی در ویژگی alt تصاویر',
            description: 'کلیدواژه کانونی در ویژگی alt تصاویر دیده نمی‌شود. بهتر است در alt تصویرهای مرتبط از آن استفاده کنید.',
            status: SeoCheckStatusEnum::IMPROVEMENT,
            meta: [
                'image_count' => $imageCount,
                'alt_count' => count($altTexts),
                'matched_alt_count' => 0,
            ],
            priority: 67,
        );
    }
}
