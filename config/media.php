<?php

return [
    'images' => [
        'max_width' => 8000,
        'max_height' => 8000,
        'max_pixels' => 40_000_000,

        'quality' => [
            'original' => 82,
            'thumbnail' => 72,
            'medium' => 76,
            'medium_large' => 80,
            'large' => 82,
        ],

        'variants' => [
            'thumbnail' => [
                'width' => 150,
            ],
            'medium' => [
                'width' => 300,
            ],
            'medium_large' => [
                'width' => 768,
            ],
            'large' => [
                'width' => 1280,
            ],
        ],
    ],
];
