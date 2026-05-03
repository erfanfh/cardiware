<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Enums\PostMediaCollectionEnum;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\ContentSlug;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostMedia;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PostsImporter
{
    private int $processed = 0;
    private int $created = 0;
    private int $skipped = 0;
    private int $failed = 0;

    private const JWPPP_VIDEO_URL_META_KEY = '_jwppp-video-url-1';

    /**
     * Change this if your attachments migration map uses another source_type.
     */
    private const WP_ATTACHMENT_SOURCE_TYPE = 'wordpress_attachment';

    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'post')
            ->orderBy('ID')
            ->chunkById(200, function ($posts) use ($onProgress) {
                foreach ($posts as $wpPost) {
                    $this->processed++;

                    try {
                        $result = $this->importPost($wpPost);

                        if ($result === 'created') {
                            $this->created++;
                        }

                        if ($result === 'skipped') {
                            $this->skipped++;
                        }
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('WordPress post import failed', [
                            'wordpress_post_id' => $wpPost->ID,
                            'post_title' => $wpPost->post_title ?? null,
                            'post_name' => $wpPost->post_name ?? null,
                            'message' => $exception->getMessage(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine(),
                            'trace' => $exception->getTraceAsString(),
                        ]);
                    }

                    if ($onProgress) {
                        $onProgress($this->stats());
                    }
                }
            }, 'ID');
    }

    public function total(): int
    {
        return DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'post')
            ->count();
    }

    public function stats(): array
    {
        return [
            'processed' => $this->processed,
            'created' => $this->created,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
        ];
    }

    private function importPost(object $wpPost): string
    {
        $existingMap = WpMigrationMap::query()
            ->where('source_type', 'wordpress_post')
            ->where('source_id', $wpPost->ID)
            ->where('target_type', Post::class)
            ->first();

        if ($existingMap) {
            $post = Post::withoutGlobalScopes()->find($existingMap->target_id);

            if ($post) {
                $galleryExtraction = $this->extractGalleryShortcodes(
                    (string)($wpPost->post_content ?: '')
                );

                $body = $this->appendJwPlayerVideoToContent(
                    (int)$wpPost->ID,
                    $this->normalizeWordPressContent($galleryExtraction['content'])
                );

                $this->updateExistingPostFromWordPress($post, $wpPost, $body);

                $this->syncImageGallery($post, $galleryExtraction['attachment_ids']);
                $this->syncSlugs($post, $wpPost);
            }

            return 'skipped';
        }

        $authorId = $this->resolveAuthorId((int)$wpPost->post_author);

        $slug = $this->makeUniqueSlug(
            $this->normalizeSlug($wpPost->post_name)
                ?: Str::slug($this->cleanText($wpPost->post_title) ?? '')
                ?: 'post-' . $wpPost->ID
        );

        $galleryExtraction = $this->extractGalleryShortcodes(
            (string)($wpPost->post_content ?: '')
        );

        $body = $this->appendJwPlayerVideoToContent(
            (int)$wpPost->ID,
            $this->normalizeWordPressContent($galleryExtraction['content'])
        );

        $post = Post::withoutGlobalScopes()->create([
            'author_id' => $authorId,
            'title' => $this->cleanText($wpPost->post_title) ?: 'Untitled Post ' . $wpPost->ID,
            'slug' => $slug,
            'excerpt' => $wpPost->post_excerpt ?: null,
            'body' => $body,
            'status' => $this->mapStatus($wpPost->post_status),
            'is_pinned' => false,
            'picture_id' => null,
            'published_at' => $this->normalizeDate($wpPost->post_date),
            'created_at' => $this->normalizeDate($wpPost->post_date),
            'updated_at' => $this->normalizeDate($wpPost->post_modified),
        ]);

        $this->syncImageGallery($post, $galleryExtraction['attachment_ids']);
        $this->syncSlugs($post, $wpPost);

        WpMigrationMap::query()->create([
            'source_type' => 'wordpress_post',
            'source_id' => $wpPost->ID,
            'target_type' => Post::class,
            'target_id' => $post->id,
            'meta' => [
                'post_name' => $wpPost->post_name,
                'normalized_post_name' => $slug,
                'post_status' => $wpPost->post_status,
                'post_type' => $wpPost->post_type,
                'post_author' => $wpPost->post_author,
                'guid' => $wpPost->guid,
            ],
        ]);

        return 'created';
    }

    private function updateExistingPostFromWordPress(Post $post, object $wpPost, string $body): void
    {
        $post->timestamps = false;

        $post->forceFill([
            'slug' => $this->normalizeSlug($post->slug),
            'body' => $body,
            'published_at' => $this->normalizeDate($wpPost->post_date),
            'created_at' => $this->normalizeDate($wpPost->post_date),
            'updated_at' => $this->normalizeDate($wpPost->post_modified),
        ])->save();

        $post->timestamps = true;
    }

    private function extractGalleryShortcodes(string $content): array
    {
        $attachmentIds = [];

        $content = preg_replace_callback(
            '/\[gallery\b([^\]]*)\]/i',
            function (array $matches) use (&$attachmentIds) {
                $attributes = $this->parseShortcodeAttributes($matches[1] ?? '');

                if (!empty($attributes['ids'])) {
                    $ids = collect(explode(',', $attributes['ids']))
                        ->map(fn($id) => (int)trim($id))
                        ->filter()
                        ->values()
                        ->all();

                    $attachmentIds = array_merge($attachmentIds, $ids);
                }

                return '';
            },
            $content
        );

        return [
            'content' => trim((string)$content),
            'attachment_ids' => collect($attachmentIds)->unique()->values()->all(),
        ];
    }

    private function parseShortcodeAttributes(string $text): array
    {
        $attributes = [];

        preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[2];
        }

        preg_match_all("/(\w+)\s*=\s*'([^']*)'/", $text, $singleQuoteMatches, PREG_SET_ORDER);

        foreach ($singleQuoteMatches as $match) {
            $attributes[$match[1]] = $match[2];
        }

        return $attributes;
    }

    private function syncImageGallery(Post $post, array $wpAttachmentIds): void
    {
        $galleryItems = $this->resolveGalleryItemsFromWpAttachments($wpAttachmentIds);

        PostMedia::query()
            ->where('post_id', $post->id)
            ->where('collection', PostMediaCollectionEnum::IMAGE_GALLERY)
            ->delete();

        foreach ($galleryItems as $index => $galleryItem) {
            PostMedia::query()->create([
                'post_id' => $post->id,
                'media_file_id' => $galleryItem['media_file_id'],
                'collection' => PostMediaCollectionEnum::IMAGE_GALLERY,
                'sort_order' => $index + 1,
                'title' => $galleryItem['title'],
                'meta' => [
                    'source' => 'wordpress_gallery_shortcode',
                    'wordpress_attachment_id' => $galleryItem['wordpress_attachment_id'],
                ],
            ]);
        }
    }

    private function resolveGalleryItemsFromWpAttachments(array $wpAttachmentIds): array
    {
        if (empty($wpAttachmentIds)) {
            return [];
        }

        $maps = WpMigrationMap::query()
            ->where('source_type', self::WP_ATTACHMENT_SOURCE_TYPE)
            ->where('target_type', MediaFile::class)
            ->whereIn('source_id', $wpAttachmentIds)
            ->get()
            ->keyBy('source_id');

        $attachmentTitles = DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'attachment')
            ->whereIn('ID', $wpAttachmentIds)
            ->pluck('post_title', 'ID');

        return collect($wpAttachmentIds)
            ->map(function (int $wpAttachmentId) use ($maps, $attachmentTitles) {
                $map = $maps->get($wpAttachmentId);

                if (!$map) {
                    logger()->warning('WordPress gallery attachment map not found', [
                        'wordpress_attachment_id' => $wpAttachmentId,
                    ]);

                    return null;
                }

                return [
                    'wordpress_attachment_id' => $wpAttachmentId,
                    'media_file_id' => $map->target_id,
                    'title' => $this->cleanText($attachmentTitles->get($wpAttachmentId)),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function appendJwPlayerVideoToContent(int $wpPostId, string $content): string
    {
        $videoUrl = $this->getJwPlayerVideoUrl($wpPostId);

        if (!$videoUrl) {
            return $content;
        }

        if (str_contains($content, $videoUrl)) {
            return $content;
        }

        $videoHtml = $this->makeVideoHtml($videoUrl);

        return trim($videoHtml . PHP_EOL . PHP_EOL . $content);
    }

    private function getJwPlayerVideoUrl(int $wpPostId): ?string
    {
        $videoUrl = DB::connection('wordpress')
            ->table('postmeta')
            ->where('post_id', $wpPostId)
            ->where('meta_key', self::JWPPP_VIDEO_URL_META_KEY)
            ->value('meta_value');

        $videoUrl = trim((string)$videoUrl);

        if ($videoUrl === '') {
            return null;
        }

        return $this->normalizeVideoUrl($videoUrl);
    }

    private function normalizeVideoUrl(string $url): string
    {
        $url = trim($url);

        if (str_starts_with($url, 'dl.moddingway.ir/')) {
            $url = 'https://' . $url;
        }

        if (str_starts_with($url, 'http://dl.moddingway.ir/')) {
            $url = 'https://' . substr($url, strlen('http://'));
        }

        return $url;
    }

    private function makeVideoHtml(string $videoUrl): string
    {
        $escapedUrl = e($videoUrl);

        return <<<HTML
<div class="wp-migrated-video">
    <video controls preload="metadata" style="width: 100%; max-width: 100%;">
        <source src="{$escapedUrl}" type="video/mp4">
    </video>
</div>
HTML;
    }

    private function syncSlugs(Post $post, object $wpPost): void
    {
        $this->ensureSlug($post, $this->normalizeSlug($post->slug), true);

        foreach ($this->getOldSlugs((int)$wpPost->ID) as $oldSlug) {
            if ($oldSlug === $post->slug) {
                continue;
            }

            $this->ensureSlug($post, $oldSlug, false);
        }
    }

    private function ensureSlug(Post $post, string $slug, bool $isCurrent): void
    {
        $slug = $this->normalizeSlug($slug);

        if ($slug === '') {
            return;
        }

        if ($isCurrent) {
            ContentSlug::query()
                ->where('slug', $slug)
                ->where(function ($query) use ($post) {
                    $query
                        ->where('sluggable_type', '!=', Post::class)
                        ->orWhere('sluggable_id', '!=', $post->id);
                })
                ->delete();

            ContentSlug::query()->updateOrCreate(
                [
                    'slug' => $slug,
                    'sluggable_type' => Post::class,
                    'sluggable_id' => $post->id,
                ],
                [
                    'is_current' => true,
                ]
            );

            return;
        }

        $slugAlreadyUsed = ContentSlug::query()
            ->where('slug', $slug)
            ->where(function ($query) use ($post) {
                $query
                    ->where('sluggable_type', '!=', Post::class)
                    ->orWhere('sluggable_id', '!=', $post->id);
            })
            ->exists();

        if ($slugAlreadyUsed) {
            return;
        }

        ContentSlug::query()->updateOrCreate(
            [
                'slug' => $slug,
                'sluggable_type' => Post::class,
                'sluggable_id' => $post->id,
            ],
            [
                'is_current' => false,
            ]
        );
    }

    private function getOldSlugs(int $wpPostId): array
    {
        return DB::connection('wordpress')
            ->table('postmeta')
            ->where('post_id', $wpPostId)
            ->where('meta_key', '_wp_old_slug')
            ->pluck('meta_value')
            ->map(fn($slug) => $this->normalizeSlug((string)$slug))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function resolveAuthorId(int $wpAuthorId): int
    {
        $map = WpMigrationMap::query()
            ->where('source_type', 'wordpress_user')
            ->where('source_id', $wpAuthorId)
            ->where('target_type', User::class)
            ->first();

        if ($map) {
            return $map->target_id;
        }

        return User::query()->value('id');
    }

    private function mapStatus(string $wpStatus): PostStatusEnum
    {
        return $wpStatus === 'publish'
            ? PostStatusEnum::ACTIVE
            : PostStatusEnum::INACTIVE;
    }

    private function makeUniqueSlug(string $slug): string
    {
        $base = $this->normalizeSlug($slug) ?: 'post';
        $slug = $base;
        $counter = 1;

        while (
        Post::withoutGlobalScopes()
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function normalizeSlug(?string $slug): string
    {
        $slug = trim((string)$slug);

        if ($slug === '') {
            return '';
        }

        return trim(rawurldecode($slug));
    }

    private function cleanText(?string $text): ?string
    {
        if (!$text) {
            return null;
        }

        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function normalizeDate(?string $date): Carbon
    {
        try {
            if (!$date || str_starts_with($date, '0000-00-00')) {
                return Carbon::create(2000, 1, 1, 0, 0, 0);
            }

            $parsed = Carbon::parse($date);

            if ($parsed->year < 1000 || $parsed->year > 9999) {
                logger()->warning('Invalid WordPress post date normalized to fallback date', [
                    'original_date' => $date,
                    'parsed_date' => $parsed->toDateTimeString(),
                    'parsed_year' => $parsed->year,
                ]);

                return Carbon::create(2000, 1, 1, 0, 0, 0);
            }

            return $parsed;
        } catch (Throwable) {
            logger()->warning('WordPress post date parse failed and normalized to fallback date', [
                'original_date' => $date,
            ]);

            return Carbon::create(2000, 1, 1, 0, 0, 0);
        }
    }

    private function normalizeWordPressContent(string $content): string
    {
        $content = trim($content);

        if ($content === '') {
            return '';
        }

        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $content = str_replace('<!--more-->', '', $content);

        return $this->wpAutop($content);
    }

    private function wpAutop(string $content): string
    {
        $content = $content . "\n";

        $allBlocks = '(?:table|thead|tbody|tfoot|caption|col|colgroup|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|summary)';

        $content = preg_replace('!(<' . $allBlocks . '[\s/>])!i', "\n\n$1", $content);
        $content = preg_replace('!(</' . $allBlocks . '>)!i', "$1\n\n", $content);
        $content = preg_replace('!(<hr\s*/?>)!i', "\n\n$1\n\n", $content);

        $content = preg_replace("/\n\n+/", "\n\n", $content);

        $paragraphs = preg_split('/\n\s*\n/', trim($content));

        $output = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                continue;
            }

            if ($this->isBlockHtml($paragraph)) {
                $output .= $paragraph . "\n\n";
                continue;
            }

            $paragraph = preg_replace("/\n/", "<br>\n", $paragraph);

            $output .= '<p>' . $paragraph . "</p>\n\n";
        }

        return trim($output);
    }

    private function isBlockHtml(string $content): bool
    {
        return preg_match('/^<(h[1-6]|hr|ul|ol|li|table|thead|tbody|tfoot|tr|td|th|div|blockquote|pre|figure|section|article|aside|header|footer|nav)\b[^>]*>/i', trim($content)) === 1;
    }
}
