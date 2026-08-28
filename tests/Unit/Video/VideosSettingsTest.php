<?php

namespace Tests\Unit\Video;

use App\Infrastructure\Services\Setting\Groups\VideosSettings;
use Tests\TestCase;

class VideosSettingsTest extends TestCase
{
    public function test_every_video_page_default_is_an_allowed_setting(): void
    {
        $settings = new VideosSettings();

        foreach (array_keys(VideosSettings::defaults()) as $key) {
            self::assertContains($key, $settings->getAllowedKeys());
        }

        self::assertContains('hero_image', $settings->getAllowedKeys());
    }
}
