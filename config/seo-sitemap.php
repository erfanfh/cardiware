<?php

return [
    'enabled' => true,

    'base_url' => env('APP_URL', 'https://moddingway.ir'),

    'chunk_size' => 1000,

    'timezone' => 'UTC',

    'preserve_yoast_routes' => true,

    'include_images' => true,

    'types' => [
        'post' => [
            'enabled' => true,
            'route_pattern' => 'post-sitemap{page}.xml',
            'include_home_in_first_page' => true,
            'include_images' => true,
        ],

        'project' => [
            'enabled' => true,
            'route_pattern' => 'project-sitemap{page}.xml',
            'include_images' => true,
        ],

        'page' => [
            'enabled' => true,
            'route_pattern' => 'page-sitemap{page}.xml',
            'include_images' => true,
        ],

        'category' => [
            'enabled' => true,
            'route_pattern' => 'category-sitemap{page}.xml',
            'include_images' => false,
        ],

        'tag' => [
            'enabled' => true,
            'route_pattern' => 'post_tag-sitemap{page}.xml',
            'include_images' => false,
        ],

        'author' => [
            'enabled' => true,
            'route_pattern' => 'author-sitemap{page}.xml',
            'include_images' => false,
        ],
    ],
];
