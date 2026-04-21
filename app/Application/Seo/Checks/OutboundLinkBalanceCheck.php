<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class OutboundLinkBalanceCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'outbound_link_balance';
    }

    public function weight(): int
    {
        return 4;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $outboundLinkCount = max(0, (int)$input->outboundLinkCount);
        $outboundFollowLinkCount = max(0, (int)$input->outboundFollowLinkCount);
        $outboundNofollowLinkCount = max(0, $outboundLinkCount - $outboundFollowLinkCount);

        if ($outboundLinkCount === 0) {
            return $this->makeResult(
                title: 'ترکیب لینک‌های خروجی',
                description: 'وقتی لینک خروجی‌ای در متن وجود نداشته باشد، بررسی ترکیب follow و nofollow ممکن نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'outbound_link_count' => 0,
                    'outbound_follow_link_count' => 0,
                    'outbound_nofollow_link_count' => 0,
                ],
                priority: 65,
            );
        }

        if ($outboundFollowLinkCount > 0 && $outboundNofollowLinkCount > 0) {
            return $this->makeResult(
                title: 'ترکیب لینک‌های خروجی',
                description: 'در این صفحه هر دو نوع لینک خروجی follow و nofollow وجود دارند. خیلی عالی!',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'outbound_link_count' => $outboundLinkCount,
                    'outbound_follow_link_count' => $outboundFollowLinkCount,
                    'outbound_nofollow_link_count' => $outboundNofollowLinkCount,
                ],
                priority: 65,
            );
        }

        return $this->makeResult(
            title: 'ترکیب لینک‌های خروجی',
            description: 'در این صفحه فقط یکی از انواع لینک‌های خروجی follow یا nofollow دیده می‌شود. در صورت نیاز می‌توانید تعادل بهتری ایجاد کنید.',
            status: SeoCheckStatusEnum::IMPROVEMENT,
            meta: [
                'outbound_link_count' => $outboundLinkCount,
                'outbound_follow_link_count' => $outboundFollowLinkCount,
                'outbound_nofollow_link_count' => $outboundNofollowLinkCount,
            ],
            priority: 65,
        );
    }
}
