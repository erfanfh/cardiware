<?php

namespace Tests\Feature\System;

use App\Application\Shared\Cache\CacheKey;
use App\Http\Controllers\Admin\System\SettingController;
use App\Infrastructure\Services\Setting\Groups\HeaderSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HeaderAnnouncementTest extends TestCase
{
    protected function tearDown(): void
    {
        app(HeaderSettings::class)->forgetCache();
        parent::tearDown();
    }

    private function renderAnnouncement(array $settings): string
    {
        app(HeaderSettings::class)->forgetCache();
        Cache::forever(CacheKey::settingsGroup('header'), array_merge([
            'announcement_active' => '1',
            'announcement_text' => 'خبر تازه',
            'announcement_button_text' => 'مشاهده',
            'announcement_button_url' => '/news/',
        ], $settings));

        return view('front.header.announcement')->render();
    }

    public function test_existing_image_is_preserved_and_explicit_color_mode_ignores_it(): void
    {
        $image = json_encode(['id' => 42, 'path' => '/storage/banner.png']);
        $html = $this->renderAnnouncement(['announcement_background_image' => $image]);
        $this->assertStringContainsString('header-announcement--image', $html);
        $this->assertStringContainsString("background-image: url('/storage/banner.png')", $html);
        $this->assertStringContainsString('header-announcement__button', $html);
        $this->assertStringNotContainsString('underline-offset', $html);

        $html = $this->renderAnnouncement([
            'announcement_background_image' => $image,
            'announcement_background_type' => 'color',
        ]);
        $this->assertStringContainsString('header-announcement--color', $html);
        $this->assertStringNotContainsString('background-image:', $html);
    }

    public function test_gradient_uses_configured_colors_angle_and_duration(): void
    {
        $html = $this->renderAnnouncement([
            'announcement_background_type' => 'gradient',
            'announcement_background_color' => '#112233',
            'announcement_gradient_color' => '#445566',
            'announcement_gradient_angle' => '90',
            'announcement_animation_duration' => '8',
        ]);
        $this->assertStringContainsString('header-announcement--gradient', $html);
        $this->assertStringContainsString('--announcement-base: #112233', $html);
        $this->assertStringContainsString('--announcement-gradient: #445566', $html);
        $this->assertStringContainsString('--announcement-angle: 90deg', $html);
        $this->assertStringContainsString('--announcement-duration: 8s', $html);
    }

    public function test_uploaded_pattern_image_renders_as_a_decorative_layer(): void
    {
        $html = $this->renderAnnouncement([
            'announcement_background_type' => 'pattern',
            'announcement_pattern_size' => '48',
            'announcement_pattern_image' => json_encode(['id' => 43, 'path' => '/storage/tile.png']),
        ]);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('class="header-announcement__pattern"', $html);
        $this->assertStringContainsString('--announcement-pattern-size: 48px', $html);
        $this->assertStringContainsString("background-image: url('/storage/tile.png')", $html);
    }

    public function test_no_pattern_is_generated_without_an_uploaded_image(): void
    {
        $html = $this->renderAnnouncement(['announcement_background_type' => 'pattern']);
        $this->assertStringContainsString('header-announcement--pattern', $html);
        $this->assertStringNotContainsString('class="header-announcement__pattern"', $html);
        $this->assertStringNotContainsString('background-image:', $html);
    }

    public function test_disabled_announcement_renders_nothing(): void
    {
        $this->assertSame('', trim($this->renderAnnouncement(['announcement_active' => '0'])));
    }

    public function test_invalid_animation_settings_are_rejected_before_saving(): void
    {
        Gate::shouldReceive('authorize')->once()->with('setting.header')->andReturnNull();
        $request = Request::create('/admin/settings/header', 'PUT', [
            'announcement_background_type' => 'invalid',
            'announcement_gradient_color' => 'invalid',
            'announcement_gradient_angle' => '361',
            'announcement_animation_duration' => '0',
            'announcement_pattern_size' => '0',
        ]);

        try {
            app(SettingController::class)->storeHeader($request);
            $this->fail('Invalid settings should not be saved.');
        } catch (ValidationException $exception) {
            $this->assertCount(5, $exception->errors());
        }
    }
}
