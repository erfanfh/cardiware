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
                'icon' => 'icons.menu-01',
                'gate' => 'post.list',
            ],
            [
                'label' => 'ایجاد نوشته',
                'route' => 'admin.posts.create',
                'icon' => 'icons.plus-sign',
                'gate' => 'post.create',
            ],
            [
                'label' => 'دسته‌ها',
                'route' => 'admin.posts.categories.index',
                'icon' => 'icons.tag-02',
                'gate' => 'category.manage',
            ],
            [
                'label' => 'برچسب‌ها',
                'route' => 'admin.posts.tags.index',
                'icon' => 'icons.tag-02',
                'gate' => 'tag.manage',
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
                'icon' => 'icons.menu-01',
                'gate' => 'page.list',
            ],
            [
                'label' => 'ایجاد برگه',
                'route' => 'admin.pages.create',
                'icon' => 'icons.plus-sign',
                'gate' => 'page.create',
            ]
        ],
    ],

    [
        'label' => 'دیدگاه‌ها',
        'route' => 'admin.comments.index',
        'icon' => 'icons.comment-01',
        'gate' => 'comment.list',
        'badge' => 'pending_comments',
    ],

    [
        'label' => 'پشتیبانی',
        'icon' => 'icons.customer-support',
        'children' => [
            [
                'label' => 'تیکت‌ها',
                'route' => 'admin.tickets.index',
                'icon' => 'icons.chat-question-01',
                'gate' => 'ticket.list',
                'badge' => 'open_tickets',
            ],
            [
                'label' => 'دپارتمان‌ها',
                'route' => 'admin.ticket-departments.index',
                'icon' => 'icons.folder-library',
                'gate' => 'ticket.manage-departments',
            ],
        ],
    ],

    [
        'label' => 'پروژه‌ها',
        'route' => 'admin.projects.index',
        'icon' => 'icons.folder-management',
        'children' => [
            [
                'label' => 'لیست پروژه‌ها',
                'route' => 'admin.projects.index',
                'icon' => 'icons.menu-01',
                'gate' => 'project.list',
            ],
            [
                'label' => 'ایجاد پروژه',
                'route' => 'admin.projects.create',
                'icon' => 'icons.plus-sign',
                'gate' => 'project.create',
            ]
        ],
    ],

    [
        'label' => 'محصولات',
        'route' => 'admin.products.index',
        'icon' => 'icons.shopping-bag-03',
        'children' => [
            [
                'label' => 'لیست محصولات',
                'route' => 'admin.products.index',
                'icon' => 'icons.menu-01',
                'gate' => 'product.list',
            ],
            [
                'label' => 'ایجاد محصول',
                'route' => 'admin.products.create',
                'icon' => 'icons.plus-sign',
                'gate' => 'product.create',
            ],
            [
                'label' => 'دسته‌ها',
                'route' => 'admin.products.categories.index',
                'icon' => 'icons.tag-02',
                'gate' => 'product.category',
            ],
            [
                'label' => 'برچسب‌ها',
                'route' => 'admin.products.tags.index',
                'icon' => 'icons.tag-02',
                'gate' => 'product.tag',
            ],
            [
                'label' => 'ویژگی‌ها',
                'route' => 'admin.products.attributes.index',
                'icon' => 'icons.sliders-horizontal',
                'gate' => 'product.attribute',
            ],
            [
                'label' => 'فیلدهای اطلاعات',
                'route' => 'admin.products.information.index',
                'icon' => 'icons.sliders-horizontal',
                'gate' => 'product.information',
            ],
        ],
    ],

    [
        'label' => 'فروشگاه',
        'icon' => 'icons.store-01',
        'children' => [
            [
                'label' => 'فروشنده ها',
                'route' => 'admin.stores.index',
                'icon' => 'icons.user-multiple-02',
                'gate' => 'store.list',
            ],
            [
                'label' => 'سفارش ها',
                'route' => 'admin.orders.index',
                'icon' => 'icons.invoice-03',
                'gate' => 'order.list',
            ],
            [
                'label' => 'کدهای تخفیف',
                'route' => 'admin.coupons.index',
                'icon' => 'icons.discount',
                'gate' => 'coupon.list',
            ],
        ],
    ],

    [
        'label' => 'کاربران',
        'route' => 'admin.users.index',
        'icon' => 'icons.user',
        'children' => [
            [
                'label' => 'لیست کاربران',
                'route' => 'admin.users.index',
                'icon' => 'icons.menu-01',
                'gate' => 'user.list',
            ],
            [
                'label' => 'ایجاد کاربر',
                'route' => 'admin.users.create',
                'icon' => 'icons.plus-sign',
                'gate' => 'user.create',
            ],
            [
                'label' => 'لیست نقش‌ها',
                'route' => 'admin.roles.index',
                'icon' => 'icons.square-unlock-02',
                'gate' => 'role.list',
            ]
        ],
    ],

    [
        'label' => 'فهرست ها',
        'icon' => 'icons.right-to-left-list-bullet',
        'children' => [
            [
                'label' => 'هدر سایت',
                'route' => 'admin.navigation-menus.show',
                'routeParameters' => ['header'],
                'icon' => 'icons.right-to-left-list-bullet',
                'gate' => 'navigation-menu.manage',
            ],
        ],
    ],

    [
        'label' => 'چیدمان صفحات',
        'icon' => 'icons.dashboard-square-setting',
        'children' => [
            [
                'label' => 'صفحه اصلی',
                'route' => 'admin.page-layouts.show',
                'routeParameters' => ['home'],
                'icon' => 'icons.home-12',
                'gate' => 'page-layout.home',
            ],
            [
                'label' => 'سایدبار',
                'route' => 'admin.page-layouts.show',
                'routeParameters' => ['sidebar'],
                'icon' => 'icons.menu-01',
                'gate' => 'page-layout.sidebar',
            ],
            [
                'label' => 'صفحه محتوا',
                'route' => 'admin.page-layouts.show',
                'routeParameters' => ['post-content'],
                'icon' => 'icons.file-empty-02',
                'gate' => 'page-layout.content',
            ],
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
                'icon' => 'icons.settings-02',
                'gate' => 'setting.general',
            ],
            [
                'label' => 'سئو',
                'route' => 'admin.settings.seo',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.seo',
            ],
            [
                'label' => 'هدر (سربرگ)',
                'route' => 'admin.settings.header',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.header',
            ],
            [
                'label' => 'فوتر (پابرگ)',
                'route' => 'admin.settings.footer',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.footer',
            ],
            [
                'label' => 'تیکت‌ها',
                'route' => 'admin.settings.ticket',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.ticket',
            ],
            [
                'label' => 'صفحه اصلی',
                'route' => 'admin.settings.home',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.home',
            ],
            [
                'label' => 'صفحه نوشته ها',
                'route' => 'admin.settings.posts',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.posts',
            ],
            [
                'label' => 'صفحه پروژه ها',
                'route' => 'admin.settings.projects',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.projects',
            ],
            [
                'label' => 'صفحه محصولات',
                'route' => 'admin.settings.products',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.products',
            ],
            [
                'label' => 'درباره ما',
                'route' => 'admin.settings.about-us',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.about-us',
            ],
            [
                'label' => 'ابزارک های سایدبار',
                'route' => 'admin.settings.sidebar-widgets',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.sidebar-widgets',
            ],
            [
                'label' => 'ورود/ثبت نام',
                'route' => 'admin.settings.auth',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.auth',
            ],
        ],
    ],
];
