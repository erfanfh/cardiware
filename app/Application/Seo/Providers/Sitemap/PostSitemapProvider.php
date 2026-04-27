<?php

namespace App\Application\Seo\Providers\Sitemap;

use App\Application\Seo\Contracts\SitemapProviderInterface;
use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostSitemapProvider implements SitemapProviderInterface
{
    public function key(): string
    {
        return 'post';
    }

    public function isEnabled(): bool
    {
        return (bool)config('seo-sitemap.types.post.enabled', true);
    }

    public function supports(string $type): bool
    {
        return $type === $this->key();
    }

    public function totalUrls(): int
    {
        return $this->baseQuery()->count() + $this->homeUrlCount();
    }

    public function totalPages(): int
    {
        return max(1, (int)ceil($this->totalUrls() / $this->chunkSize()));
    }

    /**
     * @return array<int, SitemapIndexItemDTO>
     */
    public function indexItems(): array
    {
        $items = [];

        for ($page = 1; $page <= $this->totalPages(); $page++) {
            $items[] = new SitemapIndexItemDTO(
                url: $this->sitemapUrl($page),
                lastmod: $this->lastmodForPage($page),
            );
        }

        return $items;
    }

    /**
     * @return array<int, SitemapUrlDTO>
     */
    public function urls(int $page = 1): array
    {
        $limit = $this->chunkSize();
        $offset = ($page - 1) * $limit;

        $urls = [];

        if ($page === 1 && $this->shouldIncludeHomeInFirstPage()) {
            $urls[] = new SitemapUrlDTO(
                url: url('/'),
                lastmod: $this->homeLastmod(),
                images: [],
            );

            $limit--;
        } else {
            $offset -= $this->homeUrlCount();
        }

        $posts = $this->baseQuery()
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit($limit)
            ->get();

        foreach ($posts as $post) {
            $urls[] = new SitemapUrlDTO(
                url: $this->postUrl($post),
                lastmod: $this->postLastmod($post),
                images: $this->postImages($post),
            );
        }

        return $urls;
    }

    private function baseQuery(): Builder
    {
        return Post::query()
            ->select([
                'id',
                'slug',
                'title',
                'body',
                'status',
                'published_at',
                'updated_at',
                'picture_id',
            ])
            ->with([
                'picture',
                'imageGallery.mediaFile.primaryVariant',
            ])
            ->where('status', PostStatusEnum::ACTIVE);
    }

    private function chunkSize(): int
    {
        return (int)config('seo-sitemap.chunk_size', 1000);
    }

    private function homeUrlCount(): int
    {
        return $this->shouldIncludeHomeInFirstPage() ? 1 : 0;
    }

    private function shouldIncludeHomeInFirstPage(): bool
    {
        return (bool)config('seo-sitemap.types.post.include_home_in_first_page', true);
    }

    private function sitemapUrl(int $page): string
    {
        return $page === 1
            ? url('/post-sitemap.xml')
            : url("/post-sitemap{$page}.xml");
    }

    private function postUrl(Post $post): string
    {
        return url('/' . trim($post->slug, '/') . '/');
    }

    private function postLastmod(Post $post): ?CarbonInterface
    {
        return $post->updated_at ?: $post->published_at;
    }

    private function homeLastmod(): ?CarbonInterface
    {
        $lastmod = $this->baseQuery()->max('updated_at');

        return $lastmod ? Carbon::parse($lastmod) : null;
    }

    private function lastmodForPage(int $page): ?CarbonInterface
    {
        $limit = $this->chunkSize();
        $offset = ($page - 1) * $limit;

        if ($page === 1 && $this->shouldIncludeHomeInFirstPage()) {
            $limit--;
        } else {
            $offset -= $this->homeUrlCount();
        }

        $lastmod = $this->baseQuery()
            ->orderBy('published_at')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit($limit)
            ->pluck('updated_at')
            ->filter()
            ->max();

        if (!$lastmod && $page === 1) {
            return $this->homeLastmod();
        }

        return $lastmod ? Carbon::parse($lastmod) : null;
    }

    /**
     * @return array<int, SitemapImageDTO>
     */
    private function postImages(Post $post): array
    {
        if (!config('seo-sitemap.types.post.include_images', true)) {
            return [];
        }

        return collect()
            ->merge($this->mainImage($post))
            ->merge($this->galleryImages($post))
            ->merge($this->contentImages($post))
            ->filter()
            ->unique(fn(SitemapImageDTO $image) => $image->url)
            ->values()
            ->take(20)
            ->all();
    }

    /**
     * @return array<int, SitemapImageDTO>
     */
    private function mainImage(Post $post): array
    {
        if (!$post->relationLoaded('picture')) {
            $post->loadMissing('picture');
        }

        if (!$post->picture?->url) {
            return [];
        }

        return [
            new SitemapImageDTO(
                url: $this->absoluteUrl($post->picture->url),
            ),
        ];
    }

    /**
     * @return array<int, SitemapImageDTO>
     */
    private function galleryImages(Post $post): array
    {
        if (!$post->relationLoaded('imageGallery')) {
            $post->loadMissing('imageGallery');
        }

        return $post->imageGallery
            ->filter(fn($imageGallery) => filled($imageGallery->mediaFile->url ?? null))
            ->map(fn($imageGallery) => new SitemapImageDTO(
                url: $this->absoluteUrl($imageGallery->mediaFile->url),
            ))
            ->values()
            ->all();
    }

    /**
     * @return array<int, SitemapImageDTO>
     */
    private function contentImages(Post $post): array
    {
        $content = $post->body ?? $post->content ?? null;

        if (blank($content)) {
            return [];
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);

        return collect($matches[1] ?? [])
            ->filter()
            ->map(fn(string $url) => new SitemapImageDTO(
                url: $this->absoluteUrl($url),
            ))
            ->values()
            ->all();
    }

    private function absoluteUrl(string $url): string
    {
        $url = trim($url);

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        if (Str::startsWith($url, '//')) {
            return request()->getScheme() . ':' . $url;
        }

        return url('/' . ltrim($url, '/'));
    }
}
