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
            ],
        ],
    ],

    [
        'label' => 'رسانه‌ها',
        'route' => 'admin.media.index',
        'icon' => 'icons.image-03',
        'gate' => 'media.list',
    ],

    [
        'label' => 'مودینگ تی‌وی',
        'route' => 'admin.videos.index',
        'icon' => 'icons.video-01',
        'children' => [
            ['label' => 'همه ویدیوها', 'route' => 'admin.videos.index', 'icon' => 'icons.menu-01', 'gate' => 'video.list'],
            ['label' => 'افزودن ویدیو', 'route' => 'admin.videos.create', 'icon' => 'icons.plus-sign', 'gate' => 'video.create'],
            ['label' => 'دسته‌ها', 'route' => 'admin.videos.categories.index', 'icon' => 'icons.tag-02', 'gate' => 'video.category'],
            ['label' => 'برچسب‌ها', 'route' => 'admin.videos.tags.index', 'icon' => 'icons.tag-02', 'gate' => 'video.tag'],
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
        'label' => 'نظرسنجی‌ها',
        'route' => 'admin.polls.index',
        'icon' => 'icons.chart-03',
        'children' => [
            [
                'label' => 'لیست نظرسنجی‌ها',
                'route' => 'admin.polls.index',
                'icon' => 'icons.menu-01',
                'gate' => 'poll.list',
            ],
            [
                'label' => 'ایجاد نظرسنجی',
                'route' => 'admin.polls.create',
                'icon' => 'icons.plus-sign',
                'gate' => 'poll.create',
            ],
        ],
    ],

    [
        'label' => 'پشتیبانی',
        'icon' => 'icons.customer-support',
        'badge' => 'open_tickets',
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
        'label' => 'قرعه کشی ها',
        'route' => 'admin.draw-campaigns.index',
        'icon' => 'icons.award-03',
        'children' => [
            [
                'label' => 'لیست قرعه کشی ها',
                'route' => 'admin.draw-campaigns.index',
                'icon' => 'icons.menu-01',
                'gate' => 'raffle-campaign.list',
            ],
            [
                'label' => 'ایجاد قرعه کشی',
                'route' => 'admin.draw-campaigns.create',
                'icon' => 'icons.plus-sign',
                'gate' => 'raffle-campaign.create',
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
            ],
        ],
    ],

    [
        'label' => 'فروشگاه',
        'icon' => 'icons.store-01',
        'badge' => 'pending_financial_requests',
        'children' => [
            [
                'label' => 'سفارش ها',
                'route' => 'admin.orders.index',
                'icon' => 'icons.invoice-03',
                'gate' => 'order.list',
            ],
            [
                'label' => 'سبدهای رها شده',
                'route' => 'admin.abandoned-carts.index',
                'icon' => 'icons.shopping-cart-02',
                'gate' => 'cart.abandoned',
            ],
            [
                'label' => 'درخواست وجه',
                'route' => 'admin.wallet-withdrawals.index',
                'icon' => 'icons.wallet-03',
                'gate' => 'wallet.withdrawals',
                'badge' => 'unresolved_wallet_withdrawals',
            ],
            [
                'label' => 'کدهای تخفیف',
                'route' => 'admin.coupons.index',
                'icon' => 'icons.discount',
                'gate' => 'coupon.list',
            ],
            [
                'label' => 'فروشنده ها',
                'route' => 'admin.stores.index',
                'icon' => 'icons.user-multiple-02',
                'gate' => 'store.list',
            ],
            [
                'label' => 'درخواست وجه فروشگاه‌ها',
                'route' => 'admin.store-withdrawals.index',
                'icon' => 'icons.wallet-03',
                'gate' => 'store.withdrawals',
                'badge' => 'pending_store_withdrawals',
            ],
            [
                'label' => 'بازسازی دانلودها',
                'route' => 'admin.purchase-download-resets.index',
                'icon' => 'icons.arrow-reload-horizontal',
                'gate' => 'purchase-access.reset-downloads',
            ],
            [
                'label' => 'دستگاه‌های لانچر',
                'route' => 'admin.launcher-devices.index',
                'icon' => 'icons.computer-check',
                'gate' => 'launcher-device.manage',
            ],
            [
                'label' => 'رصد امنیتی لانچر',
                'route' => 'admin.launcher-security.index',
                'icon' => 'icons.computer-check',
                'gate' => 'launcher-device.manage',
            ],
            [
                'label' => 'آپدیت لانچر',
                'route' => 'admin.launcher-releases.index',
                'icon' => 'icons.arrow-reload-horizontal',
                'gate' => 'launcher-device.manage',
            ],
            [
                'label' => 'اسلایدر لانچر',
                'route' => 'admin.launcher-slides.index',
                'icon' => 'icons.image-03',
                'gate' => 'launcher-device.manage',
            ],
            [
                'label' => 'اعلانات لانچر',
                'route' => 'admin.launcher-notifications.index',
                'icon' => 'icons.notification-01',
                'gate' => 'launcher-device.manage',
            ],
            [
                'label' => 'راهنمای لانچر',
                'route' => 'admin.launcher-guides.index',
                'icon' => 'icons.help-circle',
                'gate' => 'launcher-device.manage',
            ],
            [
                'label' => 'تحلیل فروش و درآمد',
                'route' => 'admin.analytics.index',
                'icon' => 'icons.chart-03',
                'gate' => 'analytics.view',
            ],
        ],
    ],

    [
        'label' => 'باشگاه مخاطبان',
        'icon' => 'icons.medal-06',
        'children' => [
            [
                'label' => 'قوانین کسب امتیاز',
                'route' => 'admin.earning-rules.index',
                'icon' => 'icons.star',
                'gate' => 'loyalty-rule.list',
            ],
            [
                'label' => 'کاربران باشگاه',
                'route' => 'admin.loyalty-members.index',
                'icon' => 'icons.user-multiple',
                'gate' => 'loyalty-account.list',
            ],
            [
                'label' => 'پاداش‌ها',
                'route' => 'admin.reward-offers.index',
                'icon' => 'icons.discount',
                'gate' => 'loyalty-reward.list',
            ],
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
            [
                'label' => 'مدال‌ها',
                'route' => 'admin.products.medals.index',
                'icon' => 'icons.medal-06',
                'gate' => 'product.medal',
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
            ],
        ],
    ],

    [
        'label' => 'اعضای تیم',
        'route' => 'admin.team-members.index',
        'icon' => 'icons.user-multiple',
        'children' => [
            [
                'label' => 'لیست اعضای تیم',
                'route' => 'admin.team-members.index',
                'icon' => 'icons.menu-01',
                'gate' => 'team-member.list',
            ],
            [
                'label' => 'ایجاد عضو تیم',
                'route' => 'admin.team-members.create',
                'icon' => 'icons.plus-sign',
                'gate' => 'team-member.create',
            ],
        ],
    ],

    [
        'label' => 'گزارش‌ها',
        'route' => 'admin.reports.index',
        'icon' => 'icons.chart-03',
        'gate' => 'report.view',
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
                'label' => 'اعلان‌ها',
                'route' => 'admin.settings.notifications',
                'icon' => 'icons.notification-01',
                'gate' => 'setting.general',
            ],
            [
                'label' => 'بک‌آپ خودکار',
                'route' => 'admin.settings.backup',
                'icon' => 'icons.arrow-reload-horizontal',
                'gate' => 'setting.general',
            ],
            [
                'label' => 'باشگاه مخاطبان',
                'route' => 'admin.settings.loyalty',
                'icon' => 'icons.settings-02',
                'gate' => 'loyalty-rule.list',
            ],
            [
                'label' => 'قرعه‌کشی',
                'route' => 'admin.settings.raffle',
                'icon' => 'icons.settings-02',
                'gate' => 'raffle-campaign.edit',
            ],
            [
                'label' => 'سئو',
                'route' => 'admin.settings.seo',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.seo',
            ],
            [
                'label' => 'ریدایرکت ها',
                'route' => 'admin.redirects.index',
                'icon' => 'icons.link-square-02',
                'gate' => 'redirect.manage',
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
                'label' => 'صفحه تیم',
                'route' => 'admin.settings.team',
                'icon' => 'icons.settings-02',
                'gate' => 'setting.team',
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
