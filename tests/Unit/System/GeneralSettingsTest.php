<?php

namespace Tests\Unit\System;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use PHPUnit\Framework\TestCase;

class GeneralSettingsTest extends TestCase
{
    public function test_checkout_terms_settings_are_allowed_and_enabled_flag_is_boolean(): void
    {
        $settings = new class extends GeneralSettings
        {
            public function casts(): array
            {
                return $this->casts;
            }
        };

        self::assertContains('checkout_terms_enabled', $settings->getAllowedKeys());
        self::assertContains('checkout_terms_url', $settings->getAllowedKeys());
        self::assertSame(BooleanCast::class, $settings->casts()['checkout_terms_enabled']);
    }
}
