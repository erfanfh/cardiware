<?php

namespace Tests\Feature\Navigation;

use Tests\TestCase;

class MegaMenuQuickLinksViewTest extends TestCase
{
    public function test_it_renders_only_the_configured_quick_links(): void
    {
        $html = view('front.header.navigation.mega-item', [
            'item' => $this->megaMenu([
                ['title' => 'دانلودها', 'url' => '/downloads', 'target' => '_self'],
                ['title' => 'پشتیبانی', 'url' => 'https://support.example.com', 'target' => '_blank'],
            ]),
        ])->render();

        self::assertStringContainsString('دسترسی سریع', $html);
        self::assertStringContainsString('href="/downloads"', $html);
        self::assertStringContainsString('href="https://support.example.com"', $html);
        self::assertStringContainsString('rel="noopener noreferrer"', $html);
    }

    public function test_it_hides_the_quick_access_column_when_no_links_are_configured(): void
    {
        $html = view('front.header.navigation.mega-item', [
            'item' => $this->megaMenu([]),
        ])->render();

        self::assertStringNotContainsString('دسترسی سریع', $html);
        self::assertStringContainsString('col-span-12 p-6', $html);
    }

    /** @return array<string, mixed> */
    private function megaMenu(array $quickLinks): array
    {
        return [
            'title' => 'محصولات',
            'url' => '/products',
            'target' => '_self',
            'icon' => null,
            'icon_svg' => null,
            'badge' => null,
            'css_class' => '',
            'mega_menu_title' => 'محصولات',
            'mega_menu_description' => 'دسته‌بندی محصولات',
            'mega_menu_columns' => 4,
            'mega_menu_quick_links' => $quickLinks,
            'children' => collect(),
        ];
    }
}
