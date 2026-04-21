<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class FocusKeywordInMetaDescriptionCheck extends AbstractSeoCheck
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'focus_keyword_in_meta_description';
    }

    public function weight(): int
    {
        return 7;
    }

    public function analyze(SeoAnalysisInputDTO $input): \App\Application\Seo\DTOs\SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);
        $description = trim((string)$input->resolvedSeoDescription);

        if ($keyword === '' || $description === '') {
            return $this->makeResult(
                title: 'کلیدواژه در توضیحات متا',
                description: 'برای بررسی این معیار، هم کلیدواژه کانونی و هم توضیح متا باید موجود باشند.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 85,
            );
        }

        if ($this->persianTextNormalizer->contains($description, $keyword)) {
            return $this->makeResult(
                title: 'کلیدواژه در توضیحات متا',
                description: 'کلیدواژه کانونی در توضیحات متا وجود دارد. خیلی هم عالی!',
                status: SeoCheckStatusEnum::GOOD,
                priority: 85,
            );
        }

        return $this->makeResult(
            title: 'کلیدواژه در توضیحات متا',
            description: 'کلیدواژه کانونی در توضیحات متا دیده نمی‌شود.',
            status: SeoCheckStatusEnum::PROBLEM,
            priority: 85,
        );
    }
}
