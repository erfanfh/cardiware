<?php

return [
    [
        'label' => 'پیشخوان',
        'route' => 'admin.dashboard',
        'icon' => 'icons.dashboard', // view name
        'can' => null,
        'gate' => null,
    ],

    [
        'label' => 'نوشته‌ها',
        'route' => 'admin.posts.index',
        'icon' => 'icons.file-02',
        'children' => [
            [
                'label' => 'لیست نوشته‌ها',
                'route' => 'admin.posts.index',
                'icon' => 'icons.menu-01'
            ],
            [
                'label' => 'ایجاد نوشته',
                'route' => 'admin.posts.create',
                'icon' => 'icons.plus-sign'
            ],
            [
                'label' => 'دسته‌ها',
                'route' => 'admin.categories.index',
                'icon' => 'icons.tag-02'
            ],
            [
                'label' => 'برچسب‌ها',
                'route' => 'admin.tags.index',
                'icon' => 'icons.tag-02'
            ],
        ],
    ],

    [
        'label' => 'برگه‌ها',
        'route' => 'admin.pages.index',
        'icon' => 'icons.file-empty-02',
        'children' => [
            [
                'label' => 'لیست برگه‌ها',
                'route' => 'admin.pages.index',
                'icon' => 'icons.menu-01'
            ],
            [
                'label' => 'ایجاد برگه',
                'route' => 'admin.pages.create',
                'icon' => 'icons.plus-sign'
            ]
        ],
    ],

    [
        'label' => 'دیدگاه‌ها',
        'route' => 'admin.comments.index',
        'icon' => 'icons.comment-01',
    ],

    [
        'label' => 'پروژه‌ها',
        'route' => 'admin.projects.index',
        'icon' => 'icons.folder-management',
        'children' => [
            [
                'label' => 'لیست پروژه‌ها',
                'route' => 'admin.projects.index',
                'icon' => 'icons.menu-01'
            ],
            [
                'label' => 'ایجاد پروژه',
                'route' => 'admin.projects.create',
                'icon' => 'icons.plus-sign'
            ]
        ],
    ],

    [
        'label' => 'تنظیمات سایت',
        'route' => 'admin.settings.general',
        'icon' => 'icons.settings-02',
        'children' => [
            [
                'label' => 'عمومی',
                'route' => 'admin.settings.general',
                'icon' => 'icons.settings-02'
            ],
            [
                'label' => 'هدر (سربرگ)',
                'route' => 'admin.settings.header',
                'icon' => 'icons.settings-02'
            ],
            [
                'label' => 'فوتر (پابرگ)',
                'route' => 'admin.settings.footer',
                'icon' => 'icons.settings-02'
            ],
            [
                'label' => 'صفحه اصلی',
                'route' => 'admin.settings.home',
                'icon' => 'icons.settings-02'
            ],
        ],
    ],
];
