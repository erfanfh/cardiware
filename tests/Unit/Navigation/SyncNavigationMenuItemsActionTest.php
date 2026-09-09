<?php

namespace Tests\Unit\Navigation;

use App\Application\Navigation\Actions\SyncNavigationMenuItemsAction;
use App\Application\Navigation\Support\NavigationItemUrlResolver;
use App\Application\Shared\Cache\Invalidators\NavigationCacheInvalidator;
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

    private function action(): object
    {
        return new class(
            $this->createMock(NavigationItemUrlResolver::class),
            $this->createMock(NavigationCacheInvalidator::class),
        ) extends SyncNavigationMenuItemsAction {
            /** @return array<string, mixed> */
            public function validateNodeForTest(array $item): array
            {
                return $this->validateNode($item);
            }
        };
    }
}
