<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Services\Setting\BaseSettings;

/**
 * تنظیمات یکپارچه‌سازی‌های بازارگاه (ترب‌پی، اسنپ‌پی، جستجوی ترب).
 *
 * نکته: کلیدهای API پس از عقد قرارداد از پنل ترب/اسنپ دریافت و اینجا ثبت می‌شوند.
 * تا زمانی که کلید معتبر ثبت نشده، هیچ اسکریپتی در سایت لود نمی‌شود (بدون باگ).
 */
class IntegrationsSettings extends BaseSettings
{
    protected string $group = 'integrations';

    protected array $allowedKeys = [
        'torob_pay_enabled',
        'torob_pay_merchant_key',
        'snapp_pay_enabled',
        'snapp_pay_api_key',
        'torob_search_enabled',
        'torob_search_website_id',
    ];

    protected array $casts = [
        'torob_pay_enabled' => BooleanCast::class,
        'snapp_pay_enabled' => BooleanCast::class,
        'torob_search_enabled' => BooleanCast::class,
    ];

    public function torobPayEnabled(): bool
    {
        return (bool) $this->get('torob_pay_enabled', false)
            && filled(trim((string) $this->get('torob_pay_merchant_key', '')));
    }

    public function snappPayEnabled(): bool
    {
        return (bool) $this->get('snapp_pay_enabled', false)
            && filled(trim((string) $this->get('snapp_pay_api_key', '')));
    }

    public function torobSearchEnabled(): bool
    {
        return (bool) $this->get('torob_search_enabled', false)
            && filled(trim((string) $this->get('torob_search_website_id', '')));
    }
}
