<?php

namespace App\Application\Layout\Support;

class PageLayoutElementRegistry
{
    /**
     * Get all registered page layout elements.
     *
     * @return array
     */
    public function all(): array
    {
        return [
            'story' => [
                'type' => 'story',
                'type_label' => 'استوری',
                'type_color' => 'fuchsia',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.dashboard',
            ],

            'hero-slider' => [
                'type' => 'hero_slider',
                'type_label' => 'اسلایدر',
                'type_color' => 'blue',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'slider'],
            ],

            'digital-products' => [
                'type' => 'digital_products',
                'type_label' => 'محصولات دانلودی',
                'type_color' => 'amber',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'download-products'],
            ],

            'special-posts' => [
                'type' => 'special_posts',
                'type_label' => 'مطالب ویژه',
                'type_color' => 'violet',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'special-posts'],
            ],

            'vip-button' => [
                'type' => 'vip_button',
                'type_label' => 'دکمه ویژه',
                'type_color' => 'emerald',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'content_sidebar',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'subscription'],
            ],

            'poll' => [
                'type' => 'poll',
                'type_label' => 'نظرسنجی',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'content_sidebar',
                ],
                'settings_route' => 'admin.dashboard',
            ],

            'posts' => [
                'type' => 'posts',
                'type_label' => 'مطالب',
                'type_color' => 'cyan',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'posts'],
            ],

            'services' => [
                'type' => 'services',
                'type_label' => 'سرویس‌ها',
                'type_color' => 'blue',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'services'],
            ],

            'physical-products' => [
                'type' => 'physical_products',
                'type_label' => 'محصولات فیزیکی',
                'type_color' => 'amber',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'products'],
            ],

            'projects' => [
                'type' => 'projects',
                'type_label' => 'پروژه‌ها',
                'type_color' => 'violet',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'projects'],
            ],

            'videos' => [
                'type' => 'videos',
                'type_label' => 'ویدیوها',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'videos'],
            ],

            'testimonials' => [
                'type' => 'testimonials',
                'type_label' => 'نظرات کاربران',
                'type_color' => 'cyan',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'testimonials'],
            ],

            'advertisement_1' => [
                'type' => 'advertisement',
                'type_label' => 'تبلیغات',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'content_sidebar',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.ads',
            ],

            'advertisement_2' => [
                'type' => 'advertisement',
                'type_label' => 'تبلیغات',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'content_sidebar',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.ads',
            ],

            'advertisement_3' => [
                'type' => 'advertisement',
                'type_label' => 'تبلیغات',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'content_sidebar',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.ads',
            ],

            'advertisement_4' => [
                'type' => 'advertisement',
                'type_label' => 'تبلیغات',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'content_sidebar',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.ads',
            ],

            'advertisement_5' => [
                'type' => 'advertisement',
                'type_label' => 'تبلیغات',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'content_sidebar',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.ads',
            ],
        ];
    }

    /**
     * Get a single element definition by key.
     *
     * @return array<string, mixed>|null
     */
    public function get(string $key): ?array
    {
        return $this->all()[$key] ?? null;
    }
}
