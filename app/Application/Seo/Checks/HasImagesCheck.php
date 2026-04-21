<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class HasImagesCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'has_images';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $imageCount = max(0, (int)$input->imageCount);
        $wordCount = max(0, (int)$input->wordCount);

        if ($imageCount >= 1) {
            return $this->makeResult(
                title: 'تصاویر',
                description: "این محتوا {$imageCount} تصویر دارد. کارت خوبه!",
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'image_count' => $imageCount,
                    'word_count' => $wordCount,
                ],
                priority: 68,
            );
        }

        if ($wordCount < 300) {
            return $this->makeResult(
                title: 'تصاویر',
                description: 'این محتوا تصویری ندارد. برای متن‌های کوتاه الزامی نیست، اما افزودن تصویر می‌تواند بهترش کند.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'image_count' => $imageCount,
                    'word_count' => $wordCount,
                ],
                priority: 68,
            );
        }

        return $this->makeResult(
            title: 'تصاویر',
            description: 'این محتوا هیچ تصویری ندارد. بهتر است حداقل یک تصویر مرتبط به آن اضافه کنید.',
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'image_count' => $imageCount,
                'word_count' => $wordCount,
            ],
            priority: 68,
        );
    }
}
