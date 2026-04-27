<?php

namespace App\Application\Seo\Providers\Sitemap;

use App\Application\Seo\Contracts\SitemapProviderInterface;
use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\StableSitemapPaginator;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProjectSitemapProvider implements SitemapProviderInterface
{
    public function __construct(
        private readonly StableSitemapPaginator $paginator,
    )
    {
    }

    public function key(): string
    {
        return 'project';
    }

    public function isEnabled(): bool
    {
        return (bool)config('seo-sitemap.types.project.enabled', true);
    }

    public function supports(string $type): bool
    {
        return $type === $this->key();
    }

    public function totalUrls(): int
    {
        return $this->baseIndexableQuery()->count();
    }

    public function totalPages(): int
    {
        return count($this->availablePages());
    }

    public function hasPage(int $page): bool
    {
        return in_array($page, $this->availablePages(), true);
    }

    /**
     * @return array<int, SitemapIndexItemDTO>
     */
    public function indexItems(): array
    {
        return collect($this->availablePages())
            ->map(fn(int $page) => new SitemapIndexItemDTO(
                url: $this->sitemapUrl($page),
                lastmod: $this->lastmodForPage($page),
            ))
            ->values()
            ->all();
    }

    /**
     * @return array<int, SitemapUrlDTO>
     */
    public function urls(int $page = 1): array
    {
        return $this->sitemapContentQuery()
            ->whereIn('id', $this->idsForPage($page))
            ->orderBy('id')
            ->get()
            ->map(fn(Project $project) => new SitemapUrlDTO(
                url: $this->projectUrl($project),
                lastmod: $this->projectLastmod($project),
                images: $this->projectImages($project),
            ))
            ->values()
            ->all();
    }

    private function allChunkableQuery(): Builder
    {
        return Project::query()->withTrashed();
    }

    private function baseIndexableQuery(): Builder
    {
        return Project::query()
            ->where('status', ProjectStatusEnum::ACTIVE);
    }

    private function sitemapContentQuery(): Builder
    {
        return $this->baseIndexableQuery()
            ->select([
                'id',
                'slug',
                'body',
                'updated_at',
                'cover_id',
            ])
            ->with([
                'cover',
            ]);
    }

    /**
     * @return array<int,int>
     */
    private function availablePages(): array
    {
        return $this->paginator->publicPages(
            allItemsQuery: $this->allChunkableQuery(),
            indexableQuery: $this->baseIndexableQuery(),
            chunkSize: $this->chunkSize(),
        );
    }

    /**
     * @return array<int,int>
     */
    private function idsForPage(int $page): array
    {
        return $this->paginator->idsForPublicPage(
            allItemsQuery: $this->allChunkableQuery(),
            indexableQuery: $this->baseIndexableQuery(),
            publicPage: $page,
            chunkSize: $this->chunkSize(),
        );
    }

    private function lastmodForPage(int $page): ?CarbonInterface
    {
        $ids = $this->idsForPage($page);

        if (empty($ids)) {
            return null;
        }

        $lastmod = $this->baseIndexableQuery()
            ->whereIn('id', $ids)
            ->max('updated_at');

        return $lastmod ? Carbon::parse($lastmod) : null;
    }

    private function chunkSize(): int
    {
        return max(1, (int)config('seo-sitemap.chunk_size', 1000));
    }

    private function sitemapUrl(int $page): string
    {
        return $page === 1
            ? url('/project-sitemap.xml')
            : url("/project-sitemap{$page}.xml");
    }

    private function projectUrl(Project $project): string
    {
        return front_route('projects.detail', $project);
    }

    private function projectLastmod(Project $project): ?CarbonInterface
    {
        return $project->updated_at;
    }

    /**
     * @return array<int,SitemapImageDTO>
     */
    private function projectImages(Project $project): array
    {
        if (!config('seo-sitemap.types.project.include_images', true)) {
            return [];
        }

        return collect()
            ->merge($this->coverImage($project))
            ->merge($this->contentImages($project))
            ->filter()
            ->unique(fn(SitemapImageDTO $image) => $image->url)
            ->values()
            ->take(20)
            ->all();
    }

    /**
     * @return array<int,SitemapImageDTO>
     */
    private function coverImage(Project $project): array
    {
        if (!$project->cover?->url) {
            return [];
        }

        return [
            new SitemapImageDTO(
                url: $this->absoluteUrl($project->cover->url),
            ),
        ];
    }

    /**
     * @return array<int,SitemapImageDTO>
     */
    private function contentImages(Project $project): array
    {
        if (blank($project->body)) {
            return [];
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $project->body, $matches);

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
