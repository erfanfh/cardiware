<?php

namespace Tests\Unit\Navigation;

use App\Application\Icon\Actions\ResolveIconSvgAction;
use App\Application\Navigation\Support\NavigationMenuTreeBuilder;
use App\Application\Shared\Cache\CacheKey;
use App\Domain\Navigation\Models\NavigationItem;
use Tests\TestCase;

class NavigationMenuTreeBuilderTest extends TestCase
{
    public function test_it_resolves_menu_icons_in_one_batch_and_adds_svg_to_every_admin_node(): void
    {
        $root = $this->item(
            id: 10,
            parentId: null,
            title: 'خانه',
            icon: 'lucide:home',
            settings: [
                'mega_menu_quick_links' => [
                    ['title' => 'دانلودها', 'url' => '/downloads', 'target' => '_self'],
                ],
            ],
        );

        $child = $this->item(
            id: 11,
            parentId: 10,
            title: 'مطالب',
            icon: 'lucide:folder',
        );

        $resolver = $this->createMock(ResolveIconSvgAction::class);
        $resolver->expects($this->once())
            ->method('executeMany')
            ->with(['lucide:home', 'lucide:folder'])
            ->willReturn([
                'lucide:home' => '<svg data-icon="home"></svg>',
                'lucide:folder' => '<svg data-icon="folder"></svg>',
            ]);

        $tree = (new NavigationMenuTreeBuilder($resolver))->buildForAdmin(
            collect([$root, $child]),
        );

        $this->assertSame('<svg data-icon="home"></svg>', $tree[0]['iconSvg']);
        $this->assertSame('<svg data-icon="folder"></svg>', $tree[0]['children'][0]['iconSvg']);
        $this->assertSame(
            [['title' => 'دانلودها', 'url' => '/downloads', 'target' => '_self']],
            $tree[0]['megaMenuQuickLinks'],
        );
    }

    public function test_frontend_navigation_cache_key_is_versioned_for_svg_payloads(): void
    {
        $this->assertSame(
            'navigation:frontend:v2:header',
            CacheKey::frontendNavigation('header'),
        );
    }

    private function item(
        int $id,
        ?int $parentId,
        string $title,
        string $icon,
        array $settings = [],
    ): NavigationItem {
        $item = new NavigationItem([
            'parent_id' => $parentId,
            'type' => 'custom_link',
            'title' => $title,
            'url' => '/',
            'target' => '_self',
            'icon' => $icon,
            'is_mega_menu' => false,
            'mega_menu_columns' => 4,
            'sort_order' => $id,
            'depth' => $parentId === null ? 0 : 1,
            'settings' => $settings,
        ]);

        $item->id = $id;

        return $item;
    }
}
