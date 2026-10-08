<?php

namespace Tests\Feature\System;

use App\Application\System\Actions\Footer\GetFooterNewsAction;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Admin\System\SettingController;
use App\Infrastructure\Services\Setting\Groups\FooterSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class FooterNewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_footer_form_saves_and_clears_manual_news(): void
    {
        Gate::shouldReceive('authorize')->twice()->with('setting.footer');

        $controller = app(SettingController::class);
        $controller->storeFooter(Request::create('/admin/settings/footer', 'PUT', [
            'news_enabled' => '1',
            'news_title' => 'تازه‌ها',
            'news_items' => [['title' => 'خبر سایت', 'url' => '/posts', 'sort_order' => '0']],
        ]));

        $settings = app(FooterSettings::class);
        self::assertSame('تازه‌ها', $settings->get('news_title'));
        self::assertSame('خبر سایت', $settings->get('news_items')[0]['title']);

        $controller->storeFooter(Request::create('/admin/settings/footer', 'PUT', [
            'news_enabled' => '0',
            'news_title' => '',
            'news_items' => '',
        ]));

        self::assertSame('0', $settings->get('news_enabled'));
        self::assertSame([], $settings->get('news_items'));
    }

    public function test_manual_news_preserves_order_and_rejects_unsafe_urls(): void
    {
        app(FooterSettings::class)->set('news_items', [
            ['title' => ' خبر اول ', 'url' => '/posts', 'sort_order' => 0],
            ['title' => 'خبر دوم', 'url' => 'javascript:alert(1)', 'sort_order' => 1],
        ]);

        self::assertSame([
            ['title' => 'خبر اول', 'url' => '/posts'],
            ['title' => 'خبر دوم', 'url' => ''],
        ], app(GetFooterNewsAction::class)->execute());
    }

    public function test_empty_manual_list_uses_only_published_posts(): void
    {
        app(FooterSettings::class)->set('news_items', '');

        $role = Role::factory()->create();
        $author = User::factory()->create(['role_id' => $role->id]);

        foreach ([
            ['current', 'active', now()->subDay()],
            ['older', 'active', now()->subWeek()],
            ['future', 'active', now()->addDay()],
            ['inactive', 'inactive', now()->subHour()],
        ] as [$slug, $status, $publishedAt]) {
            DB::table('posts')->insert([
                'author_id' => $author->id,
                'title' => $slug,
                'slug' => $slug,
                'excerpt' => $slug,
                'body' => $slug,
                'status' => $status,
                'published_at' => $publishedAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $items = app(GetFooterNewsAction::class)->execute();

        self::assertSame(['current', 'older'], array_column($items, 'title'));
        self::assertSame(['/current/', '/older/'], array_map(
            fn (array $item): string => parse_url($item['url'], PHP_URL_PATH),
            $items,
        ));
    }
}
