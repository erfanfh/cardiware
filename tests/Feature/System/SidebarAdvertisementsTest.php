<?php

namespace Tests\Feature\System;

use App\Application\Layout\Actions\GetPageLayoutRenderDataAction;
use App\Application\Shared\Cache\Invalidators\PageLayoutCacheInvalidator;
use App\Application\System\Actions\UpdateSettingAction;
use App\Domain\Layout\Models\PageLayout;
use App\Domain\Layout\Models\PageLayoutElement;
use Database\Seeders\Layout\PageLayoutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class SidebarAdvertisementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_advertisements_are_independent_layout_widgets(): void
    {
        $this->seed(PageLayoutSeeder::class);

        $keys = ['sidebar_advertisement_1', 'sidebar_advertisement_2'];
        $elements = PageLayoutElement::query()->whereIn('key', $keys)->get();

        self::assertCount(2, $elements);
        self::assertTrue($elements->every(fn (PageLayoutElement $element) => !$element->is_active));

        $elements->each(fn (PageLayoutElement $element) => $element->update(['is_active' => true]));
        $this->seed(PageLayoutSeeder::class);

        $layout = PageLayout::query()->where('key', 'sidebar')->firstOrFail();
        app(PageLayoutCacheInvalidator::class)->flushForLayout($layout);

        $rendered = collect(app(GetPageLayoutRenderDataAction::class)->execute('sidebar')['rows'])
            ->flatMap(fn (array $row) => $row['areas'])
            ->flatMap(fn (array $area) => $area['elements'])
            ->keyBy('key');

        self::assertSame('front.sidebar.partials.ads-1', $rendered['sidebar_advertisement_1']['view']);
        self::assertSame('front.sidebar.partials.ads-2', $rendered['sidebar_advertisement_2']['view']);
        self::assertSame(2, PageLayoutElement::query()->whereIn('key', $keys)->where('is_active', true)->count());
    }

    public function test_each_advertisement_has_its_own_full_editor_and_content(): void
    {
        View::share('errors', new ViewErrorBag());
        $form = view('admin.system.settings.partials.sidebar-ads')->render();

        self::assertStringContainsString('name="ads_1_body"', $form);
        self::assertStringContainsString('name="ads_2_body"', $form);
        self::assertSame(2, substr_count($form, 'data-editor-preset="full"'));

        $update = app(UpdateSettingAction::class);
        self::assertTrue($update->execute('sidebarWidgets', 'ads_1_body', '<p>تبلیغ اول</p>'));
        self::assertTrue($update->execute('sidebarWidgets', 'ads_2_body', '<p>تبلیغ دوم</p>'));
        self::assertTrue($update->execute('sidebarWidgets', 'ads_1_display_background', '1'));
        self::assertTrue($update->execute('sidebarWidgets', 'ads_2_display_background', '0'));

        $first = view('front.sidebar.partials.ads-1')->render();
        $second = view('front.sidebar.partials.ads-2')->render();

        self::assertStringContainsString('<p>تبلیغ اول</p>', $first);
        self::assertStringNotContainsString('تبلیغ دوم', $first);
        self::assertStringContainsString('<section', $first);
        self::assertStringContainsString('<p>تبلیغ دوم</p>', $second);
        self::assertStringNotContainsString('تبلیغ اول', $second);
        self::assertStringNotContainsString('<section', $second);
    }
}
