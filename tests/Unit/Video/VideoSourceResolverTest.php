<?php

namespace Tests\Unit\Video;

use App\Application\Video\Services\VideoSourceResolver;
use App\Domain\Video\Enums\VideoSourceTypeEnum;
use Tests\TestCase;

class VideoSourceResolverTest extends TestCase
{
    public function test_it_extracts_aparat_id_from_public_url(): void
    {
        $result = (new VideoSourceResolver())->resolve('aparat', 'https://www.aparat.com/v/AbC_123');

        self::assertSame(VideoSourceTypeEnum::APARAT, $result['source_type']);
        self::assertSame('AbC_123', $result['aparat_video_id']);
    }

    public function test_it_extracts_aparat_id_from_embed_url(): void
    {
        $result = (new VideoSourceResolver())->resolve('aparat', 'https://www.aparat.com/video/video/embed/videohash/XyZ9/vt/frame');

        self::assertSame('XyZ9', $result['aparat_video_id']);
    }

    public function test_direct_source_keeps_secure_url_and_has_no_aparat_id(): void
    {
        $result = (new VideoSourceResolver())->resolve('direct', 'https://cdn.example.com/videos/trailer.mp4');

        self::assertSame(VideoSourceTypeEnum::DIRECT, $result['source_type']);
        self::assertNull($result['aparat_video_id']);
    }

    public function test_invalid_aparat_url_is_kept_without_an_embed_id(): void
    {
        $result = (new VideoSourceResolver())->resolve('aparat', 'https://example.com/v/fake');

        self::assertSame(VideoSourceTypeEnum::APARAT, $result['source_type']);
        self::assertSame('https://example.com/v/fake', $result['video_url']);
        self::assertNull($result['aparat_video_id']);
    }

    public function test_unsafe_or_empty_urls_are_silently_normalized_to_empty(): void
    {
        $unsafe = (new VideoSourceResolver())->resolve('direct', 'javascript:alert(1)');
        $empty = (new VideoSourceResolver())->resolve('aparat', null);

        self::assertSame('', $unsafe['video_url']);
        self::assertSame('', $empty['video_url']);
        self::assertNull($empty['aparat_video_id']);
    }
}
