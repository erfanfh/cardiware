<?php

namespace App\Application\Video\Services;

use App\Domain\Video\Enums\VideoSourceTypeEnum;

final class VideoSourceResolver
{
    /** @return array{source_type: VideoSourceTypeEnum, video_url: string, aparat_video_id: ?string} */
    public function resolve(?string $sourceType, ?string $url): array
    {
        $type = VideoSourceTypeEnum::tryFrom((string)$sourceType) ?? VideoSourceTypeEnum::APARAT;
        $url = trim((string)$url);
        $parts = parse_url($url);

        if (!in_array(strtolower((string)($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            return ['source_type' => $type, 'video_url' => '', 'aparat_video_id' => null];
        }

        if ($type === VideoSourceTypeEnum::DIRECT) {
            return ['source_type' => $type, 'video_url' => $url, 'aparat_video_id' => null];
        }

        $host = strtolower((string)($parts['host'] ?? ''));
        if ($host !== 'aparat.com' && !str_ends_with($host, '.aparat.com')) {
            return ['source_type' => $type, 'video_url' => $url, 'aparat_video_id' => null];
        }

        $path = trim((string)($parts['path'] ?? ''), '/');
        $patterns = [
            '~(?:^|/)v/([a-zA-Z0-9_-]+)(?:/|$)~',
            '~(?:^|/)videohash/([a-zA-Z0-9_-]+)(?:/|$)~',
        ];
        $id = null;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $path, $matches)) {
                $id = $matches[1];
                break;
            }
        }

        return ['source_type' => $type, 'video_url' => $url, 'aparat_video_id' => $id];
    }
}
