<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class FocusKeywordInSlugCheck extends AbstractSeoCheck
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'focus_keyword_in_slug';
    }

    public function weight(): int
    {
        return 7;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);
        $slug = trim((string)$input->slug);

        if ($keyword === '' || $slug === '') {
            return $this->makeResult(
                title: 'کلیدواژه در نامک',
                description: 'برای بررسی این معیار، هم کلیدواژه کانونی و هم نامک باید موجود باشند.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 82,
            );
        }

        $keywordWords = $this->persianTextNormalizer->words($keyword);

        if ($keywordWords === []) {
            return $this->makeResult(
                title: 'کلیدواژه در نامک',
                description: 'کلیدواژه کانونی برای بررسی نامک مناسب نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 82,
            );
        }

        $normalizedSlug = $this->persianTextNormalizer->normalize(urldecode($slug));
        $slugTokens = preg_split('/[-_\s\/]+/u', $normalizedSlug, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $matched = count(array_intersect($keywordWords, $slugTokens));
        $ratio = $matched / count($keywordWords);

        if ($ratio >= 0.5) {
            return $this->makeResult(
                title: 'کلیدواژه در نامک',
                description: 'بیش از نیمی از کلیدواژه در نامک دیده می‌شود. خیلی عالی!',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'matched_ratio' => $ratio,
                    'matched_words' => $matched,
                    'total_words' => count($keywordWords),
                ],
                priority: 82,
            );
        }

        if ($ratio > 0) {
            return $this->makeResult(
                title: 'کلیدواژه در نامک',
                description: 'بخشی از کلیدواژه در نامک دیده می‌شود، اما بهتر است نامک دقیق‌تر و نزدیک‌تر به عبارت کانونی باشد.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'matched_ratio' => $ratio,
                    'matched_words' => $matched,
                    'total_words' => count($keywordWords),
                ],
                priority: 82,
            );
        }

        return $this->makeResult(
            title: 'کلیدواژه در نامک',
            description: 'کلیدواژه کانونی در نامک دیده نمی‌شود. بهتر است نامک را تغییر دهید.',
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'matched_ratio' => $ratio,
                'matched_words' => $matched,
                'total_words' => count($keywordWords),
            ],
            priority: 82,
        );
    }
}
