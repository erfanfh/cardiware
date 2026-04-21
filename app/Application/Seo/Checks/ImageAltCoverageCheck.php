<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class ImageAltCoverageCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'image_alt_coverage';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $imageCount = max(0, (int)$input->imageCount);
        $altCount = count(array_filter(
            array_map(static fn($value) => trim((string)$value), $input->imageAltTexts),
            static fn(string $value) => $value !== ''
        ));

        if ($imageCount === 0) {
            return $this->makeResult(
                title: 'پوشش alt تصاویر',
                description: 'وقتی تصویری در محتوا وجود نداشته باشد، بررسی alt تصاویر ممکن نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'image_count' => 0,
                    'alt_count' => 0,
                    'coverage_ratio' => 0,
                ],
                priority: 66,
            );
        }

        $coverageRatio = $altCount / $imageCount;

        if ($coverageRatio >= 0.8) {
            return $this->makeResult(
                title: 'پوشش alt تصاویر',
                description: 'بیشتر تصاویر این محتوا متن alt دارند. خیلی خوب است.',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'image_count' => $imageCount,
                    'alt_count' => $altCount,
                    'coverage_ratio' => round($coverageRatio, 2),
                ],
                priority: 66,
            );
        }

        if ($altCount > 0) {
            return $this->makeResult(
                title: 'پوشش alt تصاویر',
                description: 'فقط بخشی از تصاویر این محتوا متن alt دارند. بهتر است برای تصاویر بیشتری alt مناسب بنویسید.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'image_count' => $imageCount,
                    'alt_count' => $altCount,
                    'coverage_ratio' => round($coverageRatio, 2),
                ],
                priority: 66,
            );
        }

        return $this->makeResult(
            title: 'پوشش alt تصاویر',
            description: 'هیچ‌یک از تصاویر این محتوا متن alt ندارند. بهتر است برای همه تصاویر alt مناسب تعریف کنید.',
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'image_count' => $imageCount,
                'alt_count' => 0,
                'coverage_ratio' => 0,
            ],
            priority: 66,
        );
    }
}
