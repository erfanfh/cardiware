<?php

namespace Tests\Unit\System;

use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\Groups\AboutUsSettings;
use PHPUnit\Framework\TestCase;

class AboutUsSettingsTest extends TestCase
{
    public function test_every_default_is_allowed_and_structured_fields_are_cast(): void
    {
        $settings = new class extends AboutUsSettings
        {
            public function casts(): array
            {
                return $this->casts;
            }
        };

        foreach (array_keys(AboutUsSettings::defaults()) as $key) {
            self::assertContains($key, $settings->getAllowedKeys());
        }

        self::assertSame(MediaFileCast::class, $settings->casts()['hero_image']);
        self::assertSame(RepeaterArrayCast::class, $settings->casts()['what_we_build_items']);
        self::assertSame(RepeaterArrayCast::class, $settings->casts()['faqs_items']);
        self::assertSame(IntegerCast::class, $settings->casts()['projects_count']);
        self::assertSame(IntegerCast::class, $settings->casts()['team_members_count']);
    }
}
