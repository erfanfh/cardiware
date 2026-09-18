<?php

namespace Tests\Unit\System;

use App\Domain\System\Models\Setting;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\Groups\FooterSettings;
use PHPUnit\Framework\TestCase;

class FooterSettingsTest extends TestCase
{
    public function test_dark_footer_image_is_an_allowed_media_setting(): void
    {
        $settings = new class extends FooterSettings
        {
            public function casts(): array
            {
                return $this->casts;
            }
        };

        self::assertContains('column4_image_dark', $settings->getAllowedKeys());
        self::assertSame(MediaFileCast::class, $settings->casts()['column4_image_dark']);

        $cast = new MediaFileCast;
        $model = new Setting;
        $storedValue = $cast->set($model, 'column4_image_dark', [
            'id' => 42,
            'path' => 'storage/footer/dark-badges.png',
        ], []);
        $image = $cast->get($model, 'column4_image_dark', $storedValue, []);

        self::assertSame(42, $image->id);
        self::assertSame('/storage/footer/dark-badges.png', $image->path);
    }
}
