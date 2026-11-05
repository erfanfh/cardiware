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
            'club-cards' => [
                'view' => 'front.home.partials.club-cards',
            ],
            'team' => [
                'view' => 'front.home.partials.team',
            ],
            'gallery-cta' => [
                'view' => 'front.home.partials.gallery-cta',
            ],
            'featured-store' => [
                'view' => 'front.home.partials.featured-store',
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
            'content' => [
                'view' => 'front.posts.partials.detail.content',
            ],
            'image_gallery' => [
                'view' => 'front.posts.partials.detail.image-gallery',
            ],
            'video_gallery' => [
                'view' => 'front.posts.partials.detail.video',
            ],
            'related_contents' => [
                'view' => 'front.posts.partials.detail.related-posts',
            ],
            'comment_form' => [
                'view' => 'front.posts.partials.detail.comment',
            ],
            'content_comments_list' => [
                'view' => 'front.posts.partials.detail.comment-list',
            ],

            'tools' => [
                'view' => 'front.sidebar.partials.tools',
            ],
            'categories' => [
                'view' => 'front.sidebar.partials.categories',
            ],
            'sidebar_projects' => [
                'view' => 'front.sidebar.partials.projects',
            ],
            'comments' => [
                'view' => 'front.sidebar.partials.comments',
            ],
            'populars' => [
                'view' => 'front.sidebar.partials.populars',
            ],
            'sidebar_advertisement_1' => [
                'view' => 'front.sidebar.partials.ads-1',
            ],
            'sidebar_advertisement_2' => [
                'view' => 'front.sidebar.partials.ads-2',
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
