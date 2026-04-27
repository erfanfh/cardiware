<?php

namespace App\Application\Seo\Providers\Sitemap;

use App\Application\Seo\Contracts\SitemapProviderInterface;
use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapIndexItemDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProjectSitemapProvider implements SitemapProviderInterface
{
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
            ->whereBetween('id', $this->idRangeForPage($page))
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
                'title',
                'body',
                'status',
                'updated_at',
                'cover_id',
            ])
            ->with([
                'cover',
            ]);
    }

    /**
     * @return array<int, int>
     */
    private function availablePages(): array
    {
        $chunkSize = $this->chunkSize();

        return $this->baseIndexableQuery()
            ->selectRaw("FLOOR((id - 1) / {$chunkSize}) + 1 as sitemap_page")
            ->groupByRaw("FLOOR((id - 1) / {$chunkSize}) + 1")
            ->orderBy('sitemap_page')
            ->pluck('sitemap_page')
            ->map(fn($page) => (int)$page)
            ->values()
            ->all();
    }

    /**
     * @return array{0:int,1:int}
     */
    private function idRangeForPage(int $page): array
    {
        return [
            (($page - 1) * $this->chunkSize()) + 1,
            $page * $this->chunkSize(),
        ];
    }

    private function lastmodForPage(int $page): ?CarbonInterface
    {
        $lastmod = $this->baseIndexableQuery()
            ->whereBetween('id', $this->idRangeForPage($page))
            ->max('updated_at');

        return $lastmod ? Carbon::parse($lastmod) : null;
    }

    private function projectLastmod(Project $project): ?CarbonInterface
    {
        return $project->updated_at;
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
        return url('/projects/' . trim($project->slug, '/') . '/');
    }

    /**
     * @return array<int, SitemapImageDTO>
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
     * @return array<int, SitemapImageDTO>
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
     * @return array<int, SitemapImageDTO>
     */
    private function contentImages(Project $project): array
    {
        $content = $project->body ?? null;

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
