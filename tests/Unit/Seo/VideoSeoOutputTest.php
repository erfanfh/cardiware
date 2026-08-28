<?php

namespace Tests\Unit\Seo;

use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\DTOs\Sitemap\SitemapVideoDTO;
use App\Application\Seo\Renderers\SitemapXmlRenderer;
use App\Domain\Seo\Enums\OgTypeEnum;
use Tests\TestCase;

class VideoSeoOutputTest extends TestCase
{
    public function test_video_sitemap_renders_google_video_namespace_and_required_fields(): void
    {
        $entry = new SitemapUrlDTO(
            url: 'https://example.com/videos/trailer',
            videos: [new SitemapVideoDTO(
                thumbnailUrl: 'https://example.com/media/trailer.jpg',
                title: 'تریلر بازی',
                description: 'توضیح ویدیوی تست',
                playerUrl: 'https://www.aparat.com/video/video/embed/videohash/test/vt/frame',
                duration: 120,
            )],
        );

        $xml = (new SitemapXmlRenderer)->urlset([$entry]);

        self::assertStringContainsString('xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"', $xml);
        self::assertStringContainsString('<video:thumbnail_loc>https://example.com/media/trailer.jpg</video:thumbnail_loc>', $xml);
        self::assertStringContainsString('<video:title>تریلر بازی</video:title>', $xml);
        self::assertStringContainsString('<video:description>توضیح ویدیوی تست</video:description>', $xml);
        self::assertStringContainsString('<video:player_loc>https://www.aparat.com/video/video/embed/videohash/test/vt/frame</video:player_loc>', $xml);
        self::assertNotFalse(simplexml_load_string($xml));
    }

    public function test_regular_sitemap_does_not_emit_unused_video_namespace(): void
    {
        $xml = (new SitemapXmlRenderer)->urlset([
            new SitemapUrlDTO('https://example.com/posts/example'),
        ]);

        self::assertStringNotContainsString('xmlns:video=', $xml);
    }

    public function test_open_graph_emits_video_metadata(): void
    {
        $meta = (new OpenGraphDataDTO(
            type: OgTypeEnum::VIDEO,
            video: 'https://cdn.example.com/trailer.mp4',
            videoSecureUrl: 'https://cdn.example.com/trailer.mp4',
            videoType: 'video/mp4',
            videoDuration: 120,
            videoReleaseDate: '2026-08-25T10:00:00+00:00',
        ))->toMetaArray();

        self::assertSame('video.other', $meta['og:type']);
        self::assertSame('video/mp4', $meta['og:video:type']);
        self::assertSame(120, $meta['video:duration']);
    }
}
