<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\Actions\Analysis\CountOtherPostsUsingFocusKeywordAction;
use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class PreviouslyUsedFocusKeywordCheck extends AbstractSeoCheck
{
    public function __construct(
        protected CountOtherPostsUsingFocusKeywordAction $countOtherPostsUsingFocusKeywordAction,
    )
    {
    }

    public function key(): string
    {
        return 'previously_used_focus_keyword';
    }

    public function weight(): int
    {
        return 7;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);

        if ($keyword === '') {
            return $this->makeResult(
                title: 'تعداد دفعات استفاده از کلمه کلیدی',
                description: 'تا وقتی کلیدواژه کانونی تعیین نشده باشد، بررسی استفاده قبلی از آن ممکن نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 71,
            );
        }

        $usageCount = $this->countOtherPostsUsingFocusKeywordAction->execute(
            focusKeyword: $keyword,
            exceptPostId: $input->postId,
        );

        if ($usageCount === 0) {
            return $this->makeResult(
                title: 'تعداد دفعات استفاده از کلمه کلیدی',
                description: 'شما قبلاً از این کلمه کلیدی استفاده نکرده‌اید. خیلی عالی!',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'usage_count' => 0,
                ],
                priority: 71,
            );
        }

        if ($usageCount === 1) {
            return $this->makeResult(
                title: 'تعداد دفعات استفاده از کلمه کلیدی',
                description: 'این کلمه کلیدی قبلاً در یک نوشته دیگر هم استفاده شده است. بهتر است احتمال رقابت داخلی را بررسی کنید.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'usage_count' => $usageCount,
                ],
                priority: 71,
            );
        }

        return $this->makeResult(
            title: 'تعداد دفعات استفاده از کلمه کلیدی',
            description: "این کلمه کلیدی قبلاً در {$usageCount} نوشته دیگر هم استفاده شده است و ممکن است باعث رقابت داخلی شود.",
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'usage_count' => $usageCount,
            ],
            priority: 71,
        );
    }
}
