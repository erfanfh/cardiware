<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\ContentSlug;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
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
                            'message' => $exception->getMessage(),
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
                $post->forceFill([
                    'slug' => $this->normalizeSlug($post->slug),
                    'body' => $this->appendJwPlayerVideoToContent(
                        (int)$wpPost->ID,
                        $this->normalizeWordPressContent((string)($wpPost->post_content ?: ''))
                    ),
                ])->save();

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

        $body = $this->appendJwPlayerVideoToContent(
            (int)$wpPost->ID,
            $this->normalizeWordPressContent((string)($wpPost->post_content ?: ''))
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

        ContentSlug::query()->updateOrCreate(
            [
                'slug' => $slug,
                'sluggable_type' => Post::class,
                'sluggable_id' => $post->id,
            ],
            [
                'is_current' => $isCurrent,
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
                return Carbon::create(2000, 1, 1);
            }

            return Carbon::parse($date);
        } catch (Throwable) {
            return Carbon::create(2000, 1, 1);
        }
    }

    private function normalizeWordPressContent(string $content): string
    {
        $content = trim($content);

        if ($content === '') {
            return '';
        }

        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // حذف read more وردپرس
        $content = str_replace('<!--more-->', '', $content);

        return $this->wpAutop($content);
    }

    private function wpAutop(string $content): string
    {
        $content = $content . "\n";

        $allBlocks = '(?:table|thead|tbody|tfoot|caption|col|colgroup|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|summary)';

        // فاصله دادن به تگ‌های block
        $content = preg_replace('!(<' . $allBlocks . '[\s/>])!i', "\n\n$1", $content);
        $content = preg_replace('!(</' . $allBlocks . '>)!i', "$1\n\n", $content);

        // hr self closing
        $content = preg_replace('!(<hr\s*/?>)!i', "\n\n$1\n\n", $content);

        // چند خط خالی را یکی کن
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
