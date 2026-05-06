<?php

namespace App\Application\Seo\Generators\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\AbstractStaticSitemapGenerator;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PostStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    public function key(): string
    {
        return 'post';
    }

    protected function query(): Builder
    {
        return Post::query()
            ->where('status', PostStatusEnum::ACTIVE)
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
            ]);
    }

    /**
     * @return array<int, \App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO>
     */
    public function generate(): array
    {
        $indexItems = [];
        $page = 1;

        $this->query()
            ->orderBy('id')
            ->chunkById($this->chunkSize(), function ($posts) use (&$page, &$indexItems) {
                $urls = [];

                if ($page === 1 && $this->shouldIncludeHomeInFirstPage()) {
                    $urls[] = new SitemapUrlDTO(
                        url: url('/'),
                        lastmod: $this->homeLastmod(),
                        images: [],
                    );
                }

                foreach ($posts as $post) {
                    $urls[] = $this->mapModelToUrl($post);
                }

                $filename = $this->filenameForPage($page);

                $this->storage->put(
                    $filename,
                    $this->renderer->urlset($urls)
                );

                $indexItems[] = new \App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO(
                    url: $this->sitemapUrlForPage($page),
                    lastmod: $this->lastmodFromUrls($urls),
                );

                $page++;

                return true;
            });

        return $indexItems;
    }

    protected function filenameForPage(int $page): string
    {
        return $page === 1
            ? 'post-sitemap.xml'
            : "post-sitemap{$page}.xml";
    }

    protected function sitemapUrlForPage(int $page): string
    {
        return $page === 1
            ? url('/post-sitemap.xml')
            : url("/post-sitemap{$page}.xml");
    }

    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var Post $post */
        $post = $model;

        return new SitemapUrlDTO(
            url: $post->url,
            lastmod: $post->updated_at ?: $post->published_at,
            images: $this->postImages($post),
        );
    }

    private function shouldIncludeHomeInFirstPage(): bool
    {
        return (bool)config('seo-sitemap.types.post.include_home_in_first_page', true);
    }

    private function homeLastmod(): ?CarbonInterface
    {
        $lastmod = Post::query()
            ->where('status', PostStatusEnum::ACTIVE)
            ->max('updated_at');

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
        return $post->imageGallery
            ->filter(fn($imageGallery) => filled($imageGallery->mediaFile?->url))
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
        if (blank($post->body)) {
            return [];
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->body, $matches);

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
