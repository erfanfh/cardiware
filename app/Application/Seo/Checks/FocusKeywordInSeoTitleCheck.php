<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class FocusKeywordInSeoTitleCheck extends AbstractSeoCheck
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'focus_keyword_in_seo_title';
    }

    public function weight(): int
    {
        return 8;
    }

    public function analyze(SeoAnalysisInputDTO $input): \App\Application\Seo\DTOs\SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);
        $seoTitle = trim((string)$input->resolvedSeoTitle);

        if ($keyword === '' || $seoTitle === '') {
            return $this->makeResult(
                title: 'عبارت کلیدی در عنوان سئو',
                description: 'برای بررسی این معیار، هم کلیدواژه کانونی و هم عنوان سئو باید موجود باشند.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 92,
            );
        }

        $normalizedKeyword = $this->persianTextNormalizer->normalize($keyword);
        $normalizedSeoTitle = $this->persianTextNormalizer->normalize($seoTitle);

        if (str_starts_with($normalizedSeoTitle, $normalizedKeyword)) {
            return $this->makeResult(
                title: 'عبارت کلیدی در عنوان سئو',
                description: 'مطابقت دقیق کلیدواژه کانونی در ابتدای عنوان سئو ظاهر می‌شود. آفرین!',
                status: SeoCheckStatusEnum::GOOD,
                priority: 92,
            );
        }

        if (str_contains($normalizedSeoTitle, $normalizedKeyword)) {
            return $this->makeResult(
                title: 'عبارت کلیدی در عنوان سئو',
                description: 'کلیدواژه کانونی در عنوان سئو دیده می‌شود، اما بهتر است به ابتدای عنوان نزدیک‌تر باشد.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 92,
            );
        }

        return $this->makeResult(
            title: 'عبارت کلیدی در عنوان سئو',
            description: 'کلیدواژه کانونی در عنوان سئو دیده نمی‌شود.',
            status: SeoCheckStatusEnum::PROBLEM,
            priority: 92,
        );
    }
}
