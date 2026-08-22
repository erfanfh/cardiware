<?php

namespace App\Application\Raffle\Actions\Campaign;

use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Infrastructure\Services\Setting\Groups\RaffleSettings;
use Illuminate\Support\Collection;

class BuildDrawCampaignOperationalAlertsAction
{
    public function __construct(private readonly RaffleSettings $settings) {}

    public function execute(DrawCampaign $campaign): Collection
    {
        $alerts = collect();
        $now = now();

        if ($campaign->status === DrawCampaignStatusEnum::SCHEDULED && $campaign->starts_at?->lte($now)) {
            $alerts->push($this->alert('danger', 'شروع زمان‌بندی‌شده انجام نشده', 'سامانه زمان‌بندی خودکار و گزارش خطاهای سرور را بررسی کنید.'));
        }
        if ($campaign->status === DrawCampaignStatusEnum::OPEN && $campaign->closes_at?->lte($now)) {
            $alerts->push($this->alert('danger', 'پایان زمان‌بندی‌شده انجام نشده', 'کمپین بعد از زمان پایان هنوز باز است؛ سامانه زمان‌بندی خودکار را بررسی کنید.'));
        }
        if (
            $campaign->status === DrawCampaignStatusEnum::CLOSED
            && $campaign->draw_at?->lte($now)
            && ! $campaign->drawRuns()->where('type', 'initial')->exists()
        ) {
            $alerts->push($this->alert(
                'warning',
                'قرعه‌کشی آماده اجراست',
                $this->settings->get('auto_start_draw_enabled', false)
                    ? 'شروع خودکار فعال است؛ اگر تا یک دقیقه اجرا ساخته نشد، سامانه زمان‌بندی را بررسی کنید.'
                    : 'شروع خودکار غیرفعال است و ادمین باید اجرای قرعه‌کشی را آغاز کند.',
            ));
        }

        $failedRuns = $campaign->drawRuns()->where('status', DrawRunStatusEnum::FAILED->value)->count();
        if ($failedRuns > 0) {
            $alerts->push($this->alert('danger', 'اجرای ناموفق قرعه‌کشی', "{$failedRuns} اجرای ناموفق وجود دارد؛ پس از رفع خطا از صفحه اجراها دوباره تلاش کنید."));
        }

        $staleRuns = $campaign->drawRuns()
            ->where('status', DrawRunStatusEnum::RUNNING->value)
            ->where('started_at', '<=', $now->copy()->subMinutes(20))
            ->count();
        if ($staleRuns > 0) {
            $alerts->push($this->alert('danger', 'اجرای طولانی یا متوقف‌شده', 'پردازشگر صف قرعه‌کشی را بررسی کنید.'));
        }

        $overdueWinners = $campaign->winners()
            ->where('status', DrawWinnerStatusEnum::PENDING->value)
            ->whereNotNull('confirmation_deadline_at')
            ->where('confirmation_deadline_at', '<=', $now)
            ->count();
        if ($overdueWinners > 0) {
            $alerts->push($this->alert('warning', 'برنده منقضی در انتظار پردازش', "{$overdueWinners} برنده باید منقضی و با ذخیره جایگزین شود؛ سامانه زمان‌بندی را بررسی کنید."));
        }

        $smsFailures = $campaign->winners()
            ->where(function ($query): void {
                $query->where(function ($query): void {
                    $query->whereNotNull('winner_sms_failed_at')->whereNull('winner_sms_sent_at');
                })->orWhere(function ($query): void {
                    $query->whereNotNull('winner_reminder_sms_failed_at')->whereNull('winner_reminder_sms_sent_at');
                });
            })
            ->count();
        if ($smsFailures > 0) {
            $alerts->push($this->alert('danger', 'خطای پیامک برنده', "ارسال پیامک برای {$smsFailures} برنده ناموفق بوده است؛ تنظیمات قالب و خطای ثبت‌شده را بررسی کنید."));
        }

        if ($alerts->isEmpty()) {
            $alerts->push($this->alert('success', 'وضعیت عملیاتی سالم است', 'مورد معوق، اجرای شکست‌خورده یا خطای پیامکی برای این کمپین دیده نشد.'));
        }

        return $alerts;
    }

    private function alert(string $severity, string $title, string $description): array
    {
        return compact('severity', 'title', 'description');
    }
}
