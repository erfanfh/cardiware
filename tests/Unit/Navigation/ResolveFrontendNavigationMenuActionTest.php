<?php

namespace Tests\Unit\Navigation;

use App\Application\Icon\Actions\ResolveIconSvgAction;
use App\Application\Navigation\Actions\ResolveFrontendNavigationMenuAction;
use App\Application\Navigation\Support\NavigationItemUrlResolver;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Domain\Navigation\Models\NavigationMenu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ResolveFrontendNavigationMenuActionTest extends TestCase
{
    public function test_a_late_stale_write_cannot_replace_the_current_menu_revision(): void
    {
        config(['cache.default' => 'array']);
        Cache::flush();

        $menu = new NavigationMenu;
        $menu->id = 10;
        $menu->cache_revision = 0;

        $action = new class($this->createMock(NavigationItemUrlResolver::class), $this->createMock(ResolveIconSvgAction::class)) extends ResolveFrontendNavigationMenuAction
        {
            public NavigationMenu $menu;

            public Collection $resolved;

            protected function findMenu(string $slug): ?NavigationMenu
            {
                return $this->menu;
            }

            protected function resolve(int $menuId): Collection
            {
                return $this->resolved;
            }
        };

        $action->menu = $menu;
        $action->resolved = collect([['title' => 'نسخه قدیمی']]);

        $this->assertSame('نسخه قدیمی', $action->execute('header')->first()['title']);

        $menu->cache_revision = 1;
        $action->resolved = collect([['title' => 'نسخه جدید']]);

        // Simulate an old frontend request finishing after invalidation and
        // writing its stale snapshot into the previous revision's key.
        Cache::tags([
            CacheTag::NAVIGATION,
            CacheTag::navigationMenu('header'),
        ])->forever(
            CacheKey::frontendNavigation('header', 0),
            collect([['title' => 'نسخه قدیمی و دیررس']]),
        );

        $this->assertSame('نسخه جدید', $action->execute('header')->first()['title']);
        $this->assertSame(
            'نسخه قدیمی و دیررس',
            Cache::tags([
                CacheTag::NAVIGATION,
                CacheTag::navigationMenu('header'),
            ])->get(CacheKey::frontendNavigation('header', 0))->first()['title'],
        );
    }
}
