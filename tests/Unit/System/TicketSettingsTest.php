<?php

namespace Tests\Unit\System;

use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Services\Setting\Groups\TicketSettings;
use PHPUnit\Framework\TestCase;

class TicketSettingsTest extends TestCase
{
    public function test_attachment_settings_are_allowed_and_integer_casted(): void
    {
        $settings = new class extends TicketSettings
        {
            public function casts(): array
            {
                return $this->casts;
            }
        };

        foreach ([
            'attachment_max_size_mb',
            'attachments_per_message',
            'attachment_retention_days',
        ] as $key) {
            self::assertContains($key, $settings->getAllowedKeys());
            self::assertSame(IntegerCast::class, $settings->casts()[$key]);
        }

        self::assertSame(5, TicketSettings::DEFAULT_ATTACHMENT_MAX_SIZE_MB);
        self::assertSame(4, TicketSettings::DEFAULT_ATTACHMENTS_PER_MESSAGE);
        self::assertSame(90, TicketSettings::DEFAULT_ATTACHMENT_RETENTION_DAYS);
    }
}
