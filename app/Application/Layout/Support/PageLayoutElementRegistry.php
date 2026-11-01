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
                'settings_route' => 'admin.stories.index',
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
            'club-cards' => [
                'type' => 'club_cards',
                'type_label' => 'کارت‌های باشگاه (ویژه/پروژه/برترین‌ها)',
                'type_color' => 'amber',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'projects'],
            ],
            'team' => [
                'type' => 'team',
                'type_label' => 'تیم مودینگ‌وی',
                'type_color' => 'blue',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.settings.team',
            ],
            'gallery-cta' => [
                'type' => 'gallery_cta',
                'type_label' => 'باکس دعوت به گالری کاربران',
                'type_color' => 'amber',
                'allowed_areas' => [
                    'header_primary',
                    'content_main',
                    'footer_primary',
                ],
                'settings_route' => 'admin.gallery.index',
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
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'ads'],
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
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'ads'],
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
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'ads'],
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
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'ads'],
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
                'settings_route' => 'admin.settings.home',
                'settings_route_parameters' => ['tab' => 'ads'],
            ],

            'content' => [
                'type' => 'content',
                'type_label' => 'متن بدنه',
                'type_color' => 'orange',
                'allowed_areas' => [
                    'post_content_main',
                ],
            ],
            'image_gallery' => [
                'type' => 'image_gallery',
                'type_label' => 'گالری تصاویر',
                'type_color' => 'fuchsia',
                'allowed_areas' => [
                    'post_content_main',
                ],
            ],
            'video_gallery' => [
                'type' => 'video_gallery',
                'type_label' => 'گالری ویدیویی',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'post_content_main',
                ],
            ],
            'related_contents' => [
                'type' => 'related_contents',
                'type_label' => 'نوشته‌های مرتبط',
                'type_color' => 'indigo',
                'allowed_areas' => [
                    'post_content_main',
                ],
            ],
            'comment_form' => [
                'type' => 'comment_form',
                'type_label' => 'فرم ارسال دیدگاه',
                'type_color' => 'pink',
                'allowed_areas' => [
                    'post_content_main',
                ],
            ],
            'content_comments_list' => [
                'type' => 'content_comments_list',
                'type_label' => 'لیست دیدگاه‌های نوشته',
                'type_color' => 'teal',
                'allowed_areas' => [
                    'post_content_main',
                ],
            ],

            'tools' => [
                'type' => 'tools',
                'type_label' => 'ابزارهای کاربردی',
                'type_color' => 'sky',
                'allowed_areas' => [
                    'sidebar_area',
                ],
                'settings_route' => 'admin.settings.sidebar-widgets',
                'settings_route_parameters' => ['tab' => 'tools'],
            ],
            'categories' => [
                'type' => 'categories',
                'type_label' => 'دسته‌بندی‌ها',
                'type_color' => 'orange',
                'allowed_areas' => [
                    'sidebar_area',
                ],
                'settings_route' => 'admin.settings.sidebar-widgets',
                'settings_route_parameters' => ['tab' => 'categories'],
            ],
            'sidebar_projects' => [
                'type' => 'projects',
                'type_label' => 'پروژه ها',
                'type_color' => 'pink',
                'allowed_areas' => [
                    'sidebar_area',
                ],
                'settings_route' => 'admin.settings.sidebar-widgets',
                'settings_route_parameters' => ['tab' => 'projects'],
            ],
            'comments' => [
                'type' => 'comments',
                'type_label' => 'آخرین دیدگاه‌ها',
                'type_color' => 'teal',
                'allowed_areas' => [
                    'sidebar_area',
                ],
                'settings_route' => 'admin.settings.sidebar-widgets',
                'settings_route_parameters' => ['tab' => 'comments'],
            ],
            'populars' => [
                'type' => 'populars',
                'type_label' => 'مطالب پربازدید',
                'type_color' => 'indigo',
                'allowed_areas' => [
                    'sidebar_area',
                ],
                'settings_route' => 'admin.settings.sidebar-widgets',
                'settings_route_parameters' => ['tab' => 'populars'],
            ],
            'sidebar_advertisement_1' => [
                'type' => 'advertisement',
                'type_label' => 'تبلیغات سایدبار ۱',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'sidebar_area',
                ],
                'settings_route' => 'admin.settings.sidebar-widgets',
                'settings_route_parameters' => ['tab' => 'ads'],
            ],
            'sidebar_advertisement_2' => [
                'type' => 'advertisement',
                'type_label' => 'تبلیغات سایدبار ۲',
                'type_color' => 'rose',
                'allowed_areas' => [
                    'sidebar_area',
                ],
                'settings_route' => 'admin.settings.sidebar-widgets',
                'settings_route_parameters' => ['tab' => 'ads'],
            ],
        ];
    }

    /**
     * Get a single element definition by key.
     *
     * @param string $key
     * @return array<string, mixed>|null
     */
    public function get(string $key): ?array
    {
        return $this->all()[$key] ?? null;
    }
}
