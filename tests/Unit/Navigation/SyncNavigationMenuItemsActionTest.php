<?php

namespace Tests\Unit\Navigation;

use App\Application\Navigation\Actions\SyncNavigationMenuItemsAction;
use App\Application\Navigation\Support\NavigationItemUrlResolver;
use App\Application\Shared\Cache\Invalidators\NavigationCacheInvalidator;
use App\Domain\Navigation\Models\NavigationMenu;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SyncNavigationMenuItemsActionTest extends TestCase
{
    public function test_it_normalizes_custom_quick_links_for_a_mega_menu(): void
    {
        $validated = $this->action()->validateNodeForTest([
            'type' => 'custom_link',
            'title' => 'محصولات',
            'url' => '/products',
            'target' => '_self',
            'is_mega_menu' => true,
            'mega_menu_quick_links' => [
                ['title' => ' دانلودها ', 'url' => ' /downloads ', 'target' => '_self'],
                ['title' => 'سایت خارجی', 'url' => 'https://example.com/path', 'target' => '_blank'],
                ['title' => '', 'url' => '', 'target' => '_self'],
            ],
        ]);

        self::assertSame([
            ['title' => 'دانلودها', 'url' => '/downloads', 'target' => '_self'],
            ['title' => 'سایت خارجی', 'url' => 'https://example.com/path', 'target' => '_blank'],
        ], $validated['mega_menu_quick_links']);
    }

    public function test_it_rejects_unsafe_quick_link_urls(): void
    {
        $this->expectException(ValidationException::class);

        $this->action()->validateNodeForTest([
            'type' => 'custom_link',
            'title' => 'محصولات',
            'url' => '/products',
            'target' => '_self',
            'is_mega_menu' => true,
            'mega_menu_quick_links' => [
                ['title' => 'لینک ناامن', 'url' => 'javascript:alert(1)', 'target' => '_self'],
            ],
        ]);
    }

    public function test_it_bumps_the_menu_cache_revision_and_flushes_after_commit(): void
    {
        $items = \Mockery::mock(HasMany::class);
        $items->shouldReceive('delete')->once();

        $menu = \Mockery::mock(NavigationMenu::class)->shouldAllowMockingProtectedMethods();
        $menu->shouldReceive('items')->once()->andReturn($items);
        $menu->shouldReceive('increment')->once()->with('cache_revision');

        $invalidator = $this->createMock(NavigationCacheInvalidator::class);
        $invalidator->expects($this->once())->method('flushForMenu')->with($menu);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (callable $callback) => $callback());
        DB::shouldReceive('afterCommit')
            ->once()
            ->andReturnUsing(fn (callable $callback) => $callback());

        $action = new SyncNavigationMenuItemsAction(
            $this->createMock(NavigationItemUrlResolver::class),
            $invalidator,
        );

        $action->execute($menu, []);
    }

    private function action(): object
    {
        return new class($this->createMock(NavigationItemUrlResolver::class), $this->createMock(NavigationCacheInvalidator::class)) extends SyncNavigationMenuItemsAction
        {
            /** @return array<string, mixed> */
            public function validateNodeForTest(array $item): array
            {
                return $this->validateNode($item);
            }
        };
    }
}
