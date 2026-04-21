<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
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

    public function analyze(SeoAnalysisInputDTO $input): \App\Application\Seo\DTOs\SeoCheckResultDTO
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

        $matched = 0;

        foreach ($keywordWords as $word) {
            if ($word !== '' && str_contains($slug, str_replace(' ', '-', $word))) {
                $matched++;
            }
        }

        $ratio = $matched / count($keywordWords);

        if ($ratio >= 0.5) {
            return $this->makeResult(
                title: 'کلیدواژه در نامک',
                description: 'بیش از نیمی از کلیدواژه در نامک دیده می‌شود. خیلی عالی!',
                status: SeoCheckStatusEnum::GOOD,
                meta: ['matched_ratio' => $ratio],
                priority: 82,
            );
        }

        if ($ratio > 0) {
            return $this->makeResult(
                title: 'کلیدواژه در نامک',
                description: 'بخشی از کلیدواژه در نامک دیده می‌شود، اما بهتر است نامک دقیق‌تر شود.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: ['matched_ratio' => $ratio],
                priority: 82,
            );
        }

        return $this->makeResult(
            title: 'کلیدواژه در نامک',
            description: 'بخشی از کلیدواژه شما در نامک دیده نمی‌شود. آن را تغییر دهید.',
            status: SeoCheckStatusEnum::PROBLEM,
            meta: ['matched_ratio' => $ratio],
            priority: 82,
        );
    }
}
