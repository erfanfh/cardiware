<?php

namespace Tests\Feature\System;

use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Setting\Groups\SidebarWidgetsSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SidebarToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);
        $this->actingAs(User::factory()->createQuietly());
    }

    public function test_admin_can_save_edit_and_clear_optional_tool_descriptions(): void
    {
        $items = [
            ['title' => 'WinRAR', 'subtitle' => '7.1', 'url' => '/winrar', 'description' => 'استخراج فایل‌های فشرده', 'sort_order' => 0],
            ['title' => 'DirectX', 'subtitle' => '', 'url' => '/directx', 'sort_order' => 1],
        ];
        $url = route('admin.settings.sidebar-widgets.store');
        $settings = app(SidebarWidgetsSettings::class);

        $this->put($url, ['tools_items' => $items])->assertSessionHasNoErrors()->assertRedirect();
        self::assertSame($items[0]['description'], $settings->get('tools_items')[0]['description']);
        self::assertSame('/directx', $settings->get('tools_items')[1]['url']);
        $this->get(route('admin.settings.sidebar-widgets'))
            ->assertOk()->assertSee('x-model="it.description"', false);

        $items[0]['description'] = 'مدیریت آرشیوها';
        $this->put($url, ['tools_items' => $items])->assertSessionHasNoErrors()->assertRedirect();
        self::assertSame('مدیریت آرشیوها', $settings->get('tools_items')[0]['description']);

        $items[0]['description'] = '';
        $this->put($url, ['tools_items' => $items])->assertSessionHasNoErrors()->assertRedirect();
        self::assertNull($settings->get('tools_items')[0]['description']);

        $this->put($url, ['tools_items' => ''])->assertSessionHasNoErrors()->assertRedirect();
        self::assertSame([], $settings->get('tools_items'));
    }

    public function test_descriptions_are_in_tooltips_and_escaped_and_older_items_still_render(): void
    {
        $description = 'استخراج آرشیو <script>alert(1)</script>';
        app(SidebarWidgetsSettings::class)->set('tools_items', [
            ['title' => 'WinRAR', 'subtitle' => '7.1', 'url' => '/winrar', 'description' => $description],
            ['title' => 'DirectX', 'subtitle' => '', 'url' => '/directx'],
            ['title' => 'Steam', 'subtitle' => '', 'url' => '/steam', 'description' => '   '],
        ]);

        $html = view('front.sidebar.partials.tools')->render();
        self::assertStringContainsString(e($description), $html);
        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringContainsString('href="/directx"', $html);
        self::assertStringContainsString('href="/steam"', $html);

        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        self::assertSame(1, $xpath->query('//*[@role="tooltip"]')->length);
        self::assertSame($description, $xpath->query('//*[@role="tooltip"]//p')->item(0)->textContent);
        self::assertSame(1, $xpath->query('//button[@type="button" and @aria-label="توضیحات WinRAR"]')->length);
        self::assertSame(0, $xpath->query('//a//button')->length);
        self::assertSame(0, $xpath->query('//a[@href="/winrar"]/p')->length);
        self::assertSame(0, $xpath->query('//a[@href="/directx"]/p | //a[@href="/steam"]/p')->length);
    }

    public function test_invalid_or_overlong_descriptions_are_rejected_before_saving(): void
    {
        $settings = app(SidebarWidgetsSettings::class);
        $settings->set('tools_items', [['title' => 'Original', 'url' => '/original']]);

        foreach ([['invalid'], str_repeat('آ', 161)] as $description) {
            $this->putJson(route('admin.settings.sidebar-widgets.store'), [
                'tools_items' => [['title' => 'Changed', 'url' => '/changed', 'description' => $description]],
            ])->assertUnprocessable()->assertJsonValidationErrors('tools_items.0.description');

            self::assertSame('Original', $settings->get('tools_items')[0]['title']);
        }
    }
}
