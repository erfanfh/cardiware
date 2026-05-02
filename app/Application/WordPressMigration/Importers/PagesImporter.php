<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Enums\PageStatusEnum;
use App\Domain\Content\Models\ContentSlug;
use App\Domain\Content\Models\Page;
use App\Domain\Identity\Models\User;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PagesImporter
{
    private int $processed = 0;
    private int $created = 0;
    private int $skipped = 0;
    private int $failed = 0;

    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'page')
            ->orderBy('ID')
            ->chunkById(200, function ($pages) use ($onProgress) {
                foreach ($pages as $wpPage) {
                    $this->processed++;

                    try {
                        $result = $this->importPage($wpPage);

                        if ($result === 'created') {
                            $this->created++;
                        }

                        if ($result === 'skipped') {
                            $this->skipped++;
                        }
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('WordPress page import failed', [
                            'wordpress_page_id' => $wpPage->ID,
                            'post_title' => $wpPage->post_title ?? null,
                            'post_name' => $wpPage->post_name ?? null,
                            'post_status' => $wpPage->post_status ?? null,
                            'post_author' => $wpPage->post_author ?? null,
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
            ->where('post_type', 'page')
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

    private function importPage(object $wpPage): string
    {
        $existingMap = WpMigrationMap::query()
            ->where('source_type', 'wordpress_page')
            ->where('source_id', $wpPage->ID)
            ->where('target_type', Page::class)
            ->first();

        if ($existingMap) {
            $page = Page::withoutGlobalScopes()->find($existingMap->target_id);

            if (!$page) {
                logger()->warning('WordPress page skipped because target Laravel page was not found', [
                    'wordpress_page_id' => $wpPage->ID,
                    'target_page_id' => $existingMap->target_id,
                    'post_title' => $wpPage->post_title ?? null,
                    'post_name' => $wpPage->post_name ?? null,
                ]);

                return 'skipped';
            }

            $page->forceFill([
                'slug' => $this->normalizeSlug($page->slug),
                'body' => $this->normalizeWordPressContent((string)($wpPage->post_content ?: '')),
            ])->save();

            $this->syncSlugs($page, $wpPage);

            return 'skipped';
        }

        $authorId = $this->resolveAuthorId((int)$wpPage->post_author);

        $slug = $this->makeUniqueSlug(
            $this->normalizeSlug($wpPage->post_name)
                ?: Str::slug($this->cleanText($wpPage->post_title) ?? '')
                ?: 'page-' . $wpPage->ID
        );

        $page = Page::withoutGlobalScopes()->create([
            'author_id' => $authorId,
            'title' => $this->cleanText($wpPage->post_title) ?: 'Untitled Page ' . $wpPage->ID,
            'slug' => $slug,
            'excerpt' => $wpPage->post_excerpt ?: null,
            'body' => $this->normalizeWordPressContent((string)($wpPage->post_content ?: '')),
            'status' => $this->mapStatus($wpPage->post_status),
            'picture_id' => null,
            'template' => null,
            'published_at' => $this->normalizeDate($wpPage->post_date),
        ]);

        $page->forceFill([
            'created_at' => $this->normalizeDate($wpPage->post_date),
            'updated_at' => $this->normalizeDate($wpPage->post_modified),
        ])->save();

        $this->syncSlugs($page, $wpPage);

        WpMigrationMap::query()->create([
            'source_type' => 'wordpress_page',
            'source_id' => $wpPage->ID,
            'target_type' => Page::class,
            'target_id' => $page->id,
            'meta' => [
                'post_name' => $wpPage->post_name,
                'normalized_post_name' => $slug,
                'post_status' => $wpPage->post_status,
                'post_type' => $wpPage->post_type,
                'post_author' => $wpPage->post_author,
                'guid' => $wpPage->guid,
            ],
        ]);

        return 'created';
    }

    private function syncSlugs(Page $page, object $wpPage): void
    {
        $this->ensureSlug($page, $this->normalizeSlug($page->slug), true);

        foreach ($this->getOldSlugs((int)$wpPage->ID) as $oldSlug) {
            if ($oldSlug === $page->slug) {
                continue;
            }

            $this->ensureSlug($page, $oldSlug, false);
        }
    }

    private function ensureSlug(Page $page, string $slug, bool $isCurrent): void
    {
        $slug = $this->normalizeSlug($slug);

        if ($slug === '') {
            return;
        }

        ContentSlug::query()->updateOrCreate(
            [
                'slug' => $slug,
                'sluggable_type' => Page::class,
                'sluggable_id' => $page->id,
            ],
            [
                'is_current' => $isCurrent,
            ]
        );
    }

    private function getOldSlugs(int $wpPageId): array
    {
        return DB::connection('wordpress')
            ->table('postmeta')
            ->where('post_id', $wpPageId)
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

    private function mapStatus(string $wpStatus): PageStatusEnum
    {
        return $wpStatus === 'publish'
            ? PageStatusEnum::ACTIVE
            : PageStatusEnum::INACTIVE;
    }

    private function makeUniqueSlug(string $slug): string
    {
        $base = $this->normalizeSlug($slug) ?: 'page';
        $slug = $base;
        $counter = 1;

        while (
        Page::withoutGlobalScopes()
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
