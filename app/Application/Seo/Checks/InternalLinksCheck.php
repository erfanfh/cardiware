<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class InternalLinksCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'internal_links';
    }

    public function weight(): int
    {
        return 6;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $internalLinkCount = max(0, (int)$input->internalLinkCount);

        if ($internalLinkCount >= 2) {
            return $this->makeResult(
                title: 'لینک‌های داخلی',
                description: 'شما به تعداد کافی لینک داخلی دارید. کارت خوبه!',
                status: SeoCheckStatusEnum::GOOD,
                meta: ['internal_link_count' => $internalLinkCount],
                priority: 74,
            );
        }

        if ($internalLinkCount === 1) {
            return $this->makeResult(
                title: 'لینک‌های داخلی',
                description: 'فقط یک لینک داخلی در متن دیده شد. بهتر است در صورت نیاز به صفحات مرتبط بیشتری لینک بدهید.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: ['internal_link_count' => $internalLinkCount],
                priority: 74,
            );
        }

        return $this->makeResult(
            title: 'لینک‌های داخلی',
            description: 'هیچ لینک داخلی در متن پیدا نشد. بهتر است به صفحات مرتبط سایت لینک بدهید.',
            status: SeoCheckStatusEnum::PROBLEM,
            meta: ['internal_link_count' => $internalLinkCount],
            priority: 74,
        );
    }
}
