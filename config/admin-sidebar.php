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
];
