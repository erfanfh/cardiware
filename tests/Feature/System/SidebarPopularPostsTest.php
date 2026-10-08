<?php

namespace Tests\Feature\System;

use App\Application\Layout\Actions\GetPageLayoutRenderDataAction;
use App\Application\Shared\Cache\Invalidators\PageLayoutCacheInvalidator;
use App\Application\System\Actions\Sidebar\GetSidebarPopularPostsAction;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Layout\Models\PageLayout;
use App\Domain\Layout\Models\PageLayoutElement;
use App\Infrastructure\Services\Setting\Groups\SidebarWidgetsSettings;
use Carbon\Carbon;
use Database\Seeders\Layout\PageLayoutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;
use Tests\TestCase;

class SidebarPopularPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_popular_posts_widget_can_be_enabled_in_sidebar_layout(): void
    {
        $this->seed(PageLayoutSeeder::class);

        $layout = PageLayout::query()->where('key', 'sidebar')->firstOrFail();
        $element = PageLayoutElement::query()->where('key', 'populars')->firstOrFail();

        self::assertFalse($element->is_active);

        $element->update(['is_active' => true]);
        app(PageLayoutCacheInvalidator::class)->flushForLayout($layout);

        $rows = app(GetPageLayoutRenderDataAction::class)->execute('sidebar')['rows'];
        $popular = collect($rows)
            ->flatMap(fn (array $row) => $row['areas'])
            ->flatMap(fn (array $area) => $area['elements'])
            ->firstWhere('key', 'populars');

        self::assertSame('front.sidebar.partials.populars', $popular['view']);
    }

    public function test_widget_ranks_published_posts_for_day_week_and_jalali_month(): void
    {
        $this->travelTo(Carbon::parse('2026-10-08 12:00:00', 'Asia/Tehran'));

        $role = Role::factory()->create();
        $author = User::factory()->create(['role_id' => $role->id]);
        app(SidebarWidgetsSettings::class)->set('populars_posts_count', 2);

        $low = $this->createPost($author, 'low', 'active', now()->subDay());
        $high = $this->createPost($author, 'high', 'active', now()->subDay());
        $middle = $this->createPost($author, 'middle', 'active', now()->subDay());
        $inactive = $this->createPost($author, 'inactive', 'inactive', now()->subDay());
        $future = $this->createPost($author, 'future', 'active', now()->addDay());
        $old = $this->createPost($author, 'old', 'active', now()->subMonth());

        $today = today()->toDateString();
        $earlierThisWeek = today()->subDay()->toDateString();
        $earlierThisMonth = today()->startOfWeek(Carbon::SATURDAY)->subDay()->toDateString();
        $previousMonth = Jalalian::fromCarbon(today())->getFirstDayOfMonth()->toCarbon()->subDay()->toDateString();

        foreach ([
            [$low, 1, $today],
            [$low, 30, $earlierThisMonth],
            [$high, 3, $today],
            [$high, 10, $earlierThisWeek],
            [$high, 1, $earlierThisMonth],
            [$middle, 6, $today],
            [$middle, 1, $earlierThisWeek],
            [$inactive, 100, $today],
            [$future, 100, $today],
            [$old, 1000, $previousMonth],
        ] as [$postId, $views, $date]) {
            DB::table('page_view_details')->insert([
                'pageable_type' => Post::class,
                'pageable_id' => $postId,
                'count' => $views,
                'date' => $date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $widget = app(GetSidebarPopularPostsAction::class)->execute();

        self::assertSame('مطالب پربازدید', $widget['title']);
        self::assertSame([$middle, $high], $widget['periods']['day']->pluck('id')->all());
        self::assertSame([$high, $middle], $widget['periods']['week']->pluck('id')->all());
        self::assertSame([$low, $high], $widget['periods']['month']->pluck('id')->all());
        self::assertSame(13, (int) $widget['periods']['week']->first()->total_views);

        $html = view('front.sidebar.partials.populars')->render();
        self::assertStringContainsString('امروز', $html);
        self::assertStringContainsString('هفته', $html);
        self::assertStringContainsString('ماه', $html);
    }

    public function test_reseeding_adds_missing_widget_and_preserves_activation(): void
    {
        $this->seed(PageLayoutSeeder::class);
        PageLayoutElement::query()->where('key', 'populars')->delete();

        $this->seed(PageLayoutSeeder::class);
        $element = PageLayoutElement::query()->where('key', 'populars')->firstOrFail();
        self::assertFalse($element->is_active);

        $element->update(['is_active' => true]);

        $this->seed(PageLayoutSeeder::class);
        $elements = PageLayoutElement::query()->where('key', 'populars')->get();

        self::assertCount(1, $elements);
        self::assertTrue($elements->first()->is_active);
    }

    private function createPost(User $author, string $slug, string $status, $publishedAt): int
    {
        return DB::table('posts')->insertGetId([
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
}
