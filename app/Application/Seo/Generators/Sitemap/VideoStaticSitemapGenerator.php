<?php

namespace App\Application\Seo\Generators\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\AbstractStaticSitemapGenerator;
use App\Domain\Video\Models\Video;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VideoStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    public function key(): string { return 'video'; }
    protected function query(): Builder
    {
        return Video::query()->whereNotNull('slug')->with('cover')->select(['id','slug','title','updated_at','cover_media_file_id']);
    }
    protected function firstPageStaticUrls(): array
    {
        $updated = Video::query()->max('updated_at');
        return [new SitemapUrlDTO(route('videos.index'), $updated ? Carbon::parse($updated) : null)];
    }
    protected function filenameForPage(int $page): string { return $page === 1 ? 'video-sitemap.xml' : "video-sitemap{$page}.xml"; }
    protected function sitemapUrlForPage(int $page): string { return url($page === 1 ? '/video-sitemap.xml' : "/video-sitemap{$page}.xml"); }
    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var Video $model */
        $image = $model->cover?->url;
        if ($image && !Str::startsWith($image, ['http://','https://'])) { $image = url('/'.ltrim($image, '/')); }
        return new SitemapUrlDTO(route('videos.show', $model->slug), $model->updated_at, $image ? [new SitemapImageDTO($image, $model->title)] : []);
    }
}
