<?php

namespace App\Application\Seo\Generators\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\AbstractStaticSitemapGenerator;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    public function key(): string
    {
        return 'project';
    }

    protected function query(): Builder
    {
        return Project::query()
            ->where('status', ProjectStatusEnum::ACTIVE)
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

    protected function filenameForPage(int $page): string
    {
        return $page === 1
            ? 'project-sitemap.xml'
            : "project-sitemap{$page}.xml";
    }

    protected function sitemapUrlForPage(int $page): string
    {
        return $page === 1
            ? url('/project-sitemap.xml')
            : url("/project-sitemap{$page}.xml");
    }

    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var Project $project */
        $project = $model;

        return new SitemapUrlDTO(
            url: front_route('projects.detail', $project),
            lastmod: $project->updated_at,
            images: $this->projectImages($project),
        );
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
