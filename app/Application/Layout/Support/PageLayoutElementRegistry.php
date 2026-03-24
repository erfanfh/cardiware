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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
            ],

            'poll' => [
                'type' => 'poll',
                'type_label' => 'نظرسنجی',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'content_sidebar',
                ],
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
                'settings_route' => 'admin.settings.general',
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
