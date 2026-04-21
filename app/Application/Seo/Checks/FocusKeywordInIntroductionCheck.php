<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class FocusKeywordInIntroductionCheck extends AbstractSeoCheck
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'focus_keyword_in_introduction';
    }

    public function weight(): int
    {
        return 8;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);
        $firstParagraphText = trim((string)$input->firstParagraphText);

        if ($keyword === '') {
            return $this->makeResult(
                title: 'کلمه کلیدی در پاراگراف اول',
                description: 'تا وقتی کلیدواژه کانونی تعیین نشده باشد، بررسی پاراگراف اول ممکن نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 86,
            );
        }

        if ($firstParagraphText === '') {
            return $this->makeResult(
                title: 'کلمه کلیدی در پاراگراف اول',
                description: 'پاراگراف اولی برای بررسی پیدا نشد.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 86,
            );
        }

        if ($this->persianTextNormalizer->contains($firstParagraphText, $keyword)) {
            return $this->makeResult(
                title: 'کلمه کلیدی در پاراگراف اول',
                description: 'کلیدواژه کانونی در پاراگراف اول دیده می‌شود. خیلی عالی!',
                status: SeoCheckStatusEnum::GOOD,
                priority: 86,
            );
        }

        return $this->makeResult(
            title: 'کلمه کلیدی در پاراگراف اول',
            description: 'کلیدواژه کانونی در پاراگراف اول دیده نمی‌شود. بهتر است آن را زودتر در متن بیاورید.',
            status: SeoCheckStatusEnum::PROBLEM,
            priority: 86,
        );
    }
}
