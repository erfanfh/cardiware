<?php

namespace App\Application\Seo\Generators\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapImageDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapVideoDTO;
use App\Application\Seo\Support\Sitemap\AbstractStaticSitemapGenerator;
use App\Domain\Video\Models\Video;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VideoStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    public function key(): string
    {
        return 'video';
    }

    protected function query(): Builder
    {
        return Video::query()->whereNotNull('slug')->with('cover')->select([
            'id', 'slug', 'title', 'excerpt', 'source_type', 'video_url', 'aparat_video_id',
            'duration_seconds', 'published_at', 'updated_at', 'cover_media_file_id',
        ]);
    }

    protected function firstPageStaticUrls(): array
    {
        $updated = Video::query()->max('updated_at');

        return [new SitemapUrlDTO(route('videos.index'), $updated ? Carbon::parse($updated) : null)];
    }

    protected function filenameForPage(int $page): string
    {
        return $page === 1 ? 'video-sitemap.xml' : "video-sitemap{$page}.xml";
    }

    protected function sitemapUrlForPage(int $page): string
    {
        return url($page === 1 ? '/video-sitemap.xml' : "/video-sitemap{$page}.xml");
    }

    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var Video $model */
        $image = $model->cover?->url;
        if ($image && ! Str::startsWith($image, ['http://', 'https://'])) {
            $image = url('/'.ltrim($image, '/'));
        }
        $playbackUrl = $model->embed_url ?: $model->video_url;
        $hasValidPlayback = filter_var($playbackUrl, FILTER_VALIDATE_URL)
            && Str::startsWith($playbackUrl, ['http://', 'https://']);
        $description = Str::limit(
            trim(strip_tags((string) $model->excerpt)) ?: 'تماشای ویدیوی «'.$model->title.'» در Cardiware.',
            2048,
            '…',
        );
        $sitemapVideo = $image && $hasValidPlayback
            ? new SitemapVideoDTO(
                thumbnailUrl: $image,
                title: Str::limit((string) $model->title, 100, '…'),
                description: $description,
                contentUrl: $model->embed_url ? null : $model->video_url,
                playerUrl: $model->embed_url,
                duration: $model->duration_seconds > 0 && $model->duration_seconds <= 28800 ? $model->duration_seconds : null,
                publicationDate: $model->published_at,
            )
            : null;

        return new SitemapUrlDTO(
            route('videos.show', $model->slug),
            $model->updated_at,
            $image ? [new SitemapImageDTO($image, $model->title)] : [],
            $sitemapVideo ? [$sitemapVideo] : [],
        );
    }
}
