<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class OutboundLinksCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'outbound_links';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $outboundLinkCount = max(0, (int)$input->outboundLinkCount);

        if ($outboundLinkCount >= 1) {
            return $this->makeResult(
                title: 'لینک‌های خروجی',
                description: 'در این صفحه لینک خروجی وجود دارد. خیلی خوب است.',
                status: SeoCheckStatusEnum::GOOD,
                meta: ['outbound_link_count' => $outboundLinkCount],
                priority: 73,
            );
        }

        return $this->makeResult(
            title: 'لینک‌های خروجی',
            description: 'هیچ لینک خروجی در متن پیدا نشد. اگر لازم است، به منابع معتبر و مرتبط لینک بدهید.',
            status: SeoCheckStatusEnum::IMPROVEMENT,
            meta: ['outbound_link_count' => $outboundLinkCount],
            priority: 73,
        );
    }
}
