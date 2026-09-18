<?php

namespace Tests\Unit\System;

use App\Domain\System\Models\Setting;
use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\Groups\TeamSettings;
use PHPUnit\Framework\TestCase;

class TeamSettingsTest extends TestCase
{
    public function test_every_default_is_allowed_and_structured_fields_are_cast(): void
    {
        $settings = new class extends TeamSettings
        {
            public function casts(): array
            {
                return $this->casts;
            }
        };

        foreach (array_keys(TeamSettings::defaults()) as $key) {
            self::assertContains($key, $settings->getAllowedKeys());
        }

        self::assertSame(IntegerCast::class, $settings->casts()['stats_since_year']);
        self::assertSame(RepeaterArrayCast::class, $settings->casts()['highlights_items']);
        self::assertSame(ArrayCast::class, $settings->casts()['member_ids']);
        self::assertSame(RepeaterArrayCast::class, $settings->casts()['process_items']);
    }

    public function test_array_cast_accepts_the_default_array_before_a_setting_is_saved(): void
    {
        $cast = new ArrayCast;

        self::assertSame(
            [3, 1, 2],
            $cast->get(new Setting, 'member_ids', [3, 1, 2], []),
        );
    }
}
