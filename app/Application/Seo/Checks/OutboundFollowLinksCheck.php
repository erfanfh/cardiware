<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class OutboundFollowLinksCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'outbound_follow_links';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $outboundLinkCount = max(0, (int)$input->outboundLinkCount);
        $outboundFollowLinkCount = max(0, (int)$input->outboundFollowLinkCount);

        if ($outboundLinkCount === 0) {
            return $this->makeResult(
                title: 'لینک‌های خروجی follow',
                description: 'وقتی لینک خروجی‌ای در متن وجود نداشته باشد، بررسی لینک‌های follow ممکن نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'outbound_link_count' => $outboundLinkCount,
                    'outbound_follow_link_count' => $outboundFollowLinkCount,
                ],
                priority: 72,
            );
        }

        if ($outboundFollowLinkCount >= 1) {
            return $this->makeResult(
                title: 'لینک‌های خروجی follow',
                description: 'حداقل یک لینک خروجی follow در متن وجود دارد. خیلی خوب است.',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'outbound_link_count' => $outboundLinkCount,
                    'outbound_follow_link_count' => $outboundFollowLinkCount,
                ],
                priority: 72,
            );
        }

        return $this->makeResult(
            title: 'لینک‌های خروجی follow',
            description: 'همه لینک‌های خروجی این متن nofollow هستند. بهتر است در صورت نیاز، به منابع معتبر مرتبط لینک follow هم داشته باشید.',
            status: SeoCheckStatusEnum::IMPROVEMENT,
            meta: [
                'outbound_link_count' => $outboundLinkCount,
                'outbound_follow_link_count' => $outboundFollowLinkCount,
            ],
            priority: 72,
        );
    }
}
