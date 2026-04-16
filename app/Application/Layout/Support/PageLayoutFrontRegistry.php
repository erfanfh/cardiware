<?php

namespace App\Application\Layout\Support;

class PageLayoutFrontRegistry
{
    /**
     * @return array[]
     */
    public function all(): array
    {
        return [
            'story' => [
                'view' => 'front.home.partials.story',
            ],
            'hero-slider' => [
                'view' => 'front.home.partials.hero-slider',
            ],
            'digital-products' => [
                'view' => 'front.home.partials.digital-products',
            ],
            'special-posts' => [
                'view' => 'front.home.partials.special-posts',
            ],
            'vip-button' => [
                'view' => 'front.home.partials.vip-box',
            ],
            'poll' => [
                'view' => 'front.home.partials.poll',
            ],
            'posts' => [
                'view' => 'front.home.partials.free-posts',
            ],
            'services' => [
                'view' => 'front.home.partials.options-box',
            ],
            'physical-products' => [
                'view' => 'front.home.partials.physical-products',
            ],
            'projects' => [
                'view' => 'front.home.partials.projects',
            ],
            'videos' => [
                'view' => 'front.home.partials.videos',
            ],
            'testimonials' => [
                'view' => 'front.home.partials.comments',
            ],
            'advertisement_1' => [
                'view' => 'front.home.partials.ads-1',
            ],
            'advertisement_2' => [
                'view' => 'front.home.partials.ads-2',
            ],
            'advertisement_3' => [
                'view' => 'front.home.partials.ads-3',
            ],
            'advertisement_4' => [
                'view' => 'front.home.partials.ads-4',
            ],
            'advertisement_5' => [
                'view' => 'front.home.partials.ads-5',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $elementKey): ?array
    {
        return $this->all()[$elementKey] ?? null;
    }
}
