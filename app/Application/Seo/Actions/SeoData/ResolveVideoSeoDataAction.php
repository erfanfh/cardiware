<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildVideoSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\Resolvers\ResolveVideoSeoPayloadAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Domain\Seo\Enums\OgTypeEnum;
use App\Domain\Video\Enums\VideoSourceTypeEnum;
use App\Domain\Video\Models\Video;

final class ResolveVideoSeoDataAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $defaultsAction,
        private readonly ResolveVideoSeoPayloadAction $payloadAction,
        private readonly BuildVideoSchemaGraphAction $graphAction,
        private readonly RenderSchemaGraphAction $renderAction,
    ) {}

    public function execute(Video $video): SeoDataDTO
    {
        $defaults = $this->defaultsAction->execute();
        $payload = $this->payloadAction->execute($video);
        $playbackUrl = $video->source_type === VideoSourceTypeEnum::APARAT
            ? $video->embed_url
            : $video->video_url;
        $openGraphVideo = $this->httpUrl($playbackUrl);

        return new SeoDataDTO(
            robots: $this->videoRobots($payload->robots ?: $defaults['robots']),
            title: $payload->title,
            description: $payload->description,
            canonicalUrl: $payload->canonicalUrl,
            author: $payload->authorName,
            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::VIDEO,
                title: $payload->ogTitle,
                description: $payload->ogDescription,
                url: $payload->canonicalUrl,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $payload->ogImage['url'] ?? null,
                imageWidth: $payload->ogImage['width'] ?? null,
                imageHeight: $payload->ogImage['height'] ?? null,
                imageType: $payload->ogImage['type'] ?? null,
                video: $openGraphVideo,
                videoSecureUrl: str_starts_with((string) $openGraphVideo, 'https://') ? $openGraphVideo : null,
                videoType: $video->source_type === VideoSourceTypeEnum::DIRECT ? $this->videoMimeType($playbackUrl) : null,
                videoDuration: $video->duration_seconds > 0 ? $video->duration_seconds : null,
                videoReleaseDate: $payload->publishedTime,
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $payload->xTitle,
                description: $payload->xDescription,
                image: $payload->xImageUrl,
                label1: $payload->authorName ? 'منتشرکننده' : null,
                data1: $payload->authorName,
                label2: $video->formatted_duration ? 'مدت ویدیو' : null,
                data2: $video->formatted_duration,
            ),
            schemaJson: $this->renderAction->execute($this->graphAction->execute($video, $payload)),
        );
    }

    private function httpUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        return filter_var($url, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $url) ? $url : null;
    }

    private function videoMimeType(?string $url): ?string
    {
        $extension = strtolower(pathinfo((string) parse_url((string) $url, PHP_URL_PATH), PATHINFO_EXTENSION));

        return match ($extension) {
            'mp4', 'm4v' => 'video/mp4',
            'webm' => 'video/webm',
            'ogv', 'ogg' => 'video/ogg',
            'mov' => 'video/quicktime',
            default => null,
        };
    }

    private function videoRobots(string $robots): string
    {
        if (str_contains(strtolower($robots), 'max-video-preview:')) {
            return $robots;
        }

        return rtrim($robots, " \t\n\r\0\x0B,").', max-video-preview:-1';
    }
}
