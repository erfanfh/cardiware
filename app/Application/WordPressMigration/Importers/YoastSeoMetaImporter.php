<?php

namespace App\Application\WordPressMigration\Importers;

use App\Application\Seo\Actions\SyncSeoMetaAction;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class YoastSeoMetaImporter
{
    private int $processed = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private int $missingMap = 0;
    private int $failed = 0;

    private const META_KEYS = [
        '_yoast_wpseo_title',
        '_yoast_wpseo_metadesc',
        '_yoast_wpseo_focuskw',
        '_yoast_wpseo_primary_category',
        '_yoast_wpseo_is_cornerstone',
        '_yoast_wpseo_focuskeywords',
        '_yoast_wpseo_keywordsynonyms',
        '_yoast_wpseo_estimated-reading-time-minutes',
    ];

    public function __construct(
        private readonly SyncSeoMetaAction $syncSeoMetaAction,
    )
    {
    }

    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('posts')
            ->whereIn('post_type', ['post', 'page'])
            ->orderBy('ID')
            ->chunkById(200, function ($wpPosts) use ($onProgress) {
                foreach ($wpPosts as $wpPost) {
                    $this->processed++;

                    try {
                        $result = $this->importSeoMeta($wpPost);

                        match ($result) {
                            'updated' => $this->updated++,
                            'skipped' => $this->skipped++,
                            'missing_map' => $this->missingMap++,
                        };
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('Yoast SEO meta import failed', [
                            'wordpress_post_id' => $wpPost->ID,
                            'post_type' => $wpPost->post_type,
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
            ->whereIn('post_type', ['post', 'page'])
            ->count();
    }

    public function stats(): array
    {
        return [
            'processed' => $this->processed,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'missing_map' => $this->missingMap,
            'failed' => $this->failed,
        ];
    }

    private function importSeoMeta(object $wpPost): string
    {
        $seoable = $this->resolveSeoable($wpPost);

        if (!$seoable) {
            return 'missing_map';
        }

        $meta = $this->getYoastMeta((int)$wpPost->ID);
        $indexable = $this->getYoastIndexable((int)$wpPost->ID, (string)$wpPost->post_type);

        if ($meta === [] && !$indexable) {
            return 'skipped';
        }

        $payload = $this->buildSeoPayload($meta, $indexable);

        if ($this->isEmptyPayload($payload)) {
            return 'skipped';
        }

        $this->syncSeoMetaAction->execute($seoable, $payload);

        return 'updated';
    }

    private function resolveSeoable(object $wpPost): ?Model
    {
        $targetClass = match ($wpPost->post_type) {
            'post' => Post::class,
            'page' => Page::class,
            default => null,
        };

        if (!$targetClass) {
            return null;
        }

        $sourceType = match ($wpPost->post_type) {
            'post' => 'wordpress_post',
            'page' => 'wordpress_page',
            default => null,
        };

        if (!$sourceType) {
            return null;
        }

        $map = WpMigrationMap::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $wpPost->ID)
            ->where('target_type', $targetClass)
            ->first();

        if (!$map) {
            return null;
        }

        return $targetClass::withoutGlobalScopes()->find($map->target_id);
    }

    /**
     * @return array<string, string|null>
     */
    private function getYoastMeta(int $wpPostId): array
    {
        return DB::connection('wordpress')
            ->table('postmeta')
            ->where('post_id', $wpPostId)
            ->whereIn('meta_key', self::META_KEYS)
            ->pluck('meta_value', 'meta_key')
            ->mapWithKeys(fn($value, $key) => [$key => $this->normalizeValue($value)])
            ->all();
    }

    private function getYoastIndexable(int $wpPostId, string $postType): ?object
    {
        return DB::connection('wordpress')
            ->table('yoast_indexable')
            ->where('object_id', $wpPostId)
            ->where('object_type', 'post')
            ->where('object_sub_type', $postType)
            ->first();
    }

    /**
     * @param array<string, string|null> $meta
     * @return array<string, mixed>
     */
    private function buildSeoPayload(array $meta, ?object $indexable): array
    {
        return [
            'focus_keyword' => $this->firstFilled(
                $meta['_yoast_wpseo_focuskw'] ?? null,
                $indexable?->primary_focus_keyword ?? null,
            ),

            // این‌ها را از yoast_indexable می‌گیریم چون خروجی نهایی Yoast هستند
            'title' => $this->firstFilled(
                $indexable?->title ?? null,
                $meta['_yoast_wpseo_title'] ?? null,
            ),

            'description' => $this->firstFilled(
                $indexable?->description ?? null,
                $meta['_yoast_wpseo_metadesc'] ?? null,
            ),

            'robots' => $this->buildRobots($indexable),

            'canonical_url' => $this->normalizeValue($indexable?->canonical ?? null),

            'og_title' => $this->normalizeValue($indexable?->open_graph_title ?? null),
            'og_description' => $this->normalizeValue($indexable?->open_graph_description ?? null),

            'x_title' => $this->normalizeValue($indexable?->twitter_title ?? null),
            'x_description' => $this->normalizeValue($indexable?->twitter_description ?? null),

            'schema_type' => $this->normalizeValue($indexable?->schema_page_type ?? null),

            'schema_settings' => [
                'yoast' => [
                    'indexable_id' => $indexable?->id ?? null,

                    'permalink' => $this->normalizeValue($indexable?->permalink ?? null),
                    'breadcrumb_title' => $this->normalizeValue($indexable?->breadcrumb_title ?? null),

                    'primary_category_wp_term_id' => $this->nullableInteger(
                        $meta['_yoast_wpseo_primary_category'] ?? null
                    ),

                    'is_cornerstone' => $this->toBoolean(
                        $meta['_yoast_wpseo_is_cornerstone'] ?? null
                    ),

                    'focus_keywords' => $this->decodeMaybeJson(
                        $meta['_yoast_wpseo_focuskeywords'] ?? null
                    ),

                    'keyword_synonyms' => $this->decodeMaybeJson(
                        $meta['_yoast_wpseo_keywordsynonyms'] ?? null
                    ),

                    'estimated_reading_time_minutes' => $this->nullableInteger(
                        $meta['_yoast_wpseo_estimated-reading-time-minutes'] ?? null
                    ),

                    'schema_page_type' => $this->normalizeValue($indexable?->schema_page_type ?? null),
                    'schema_article_type' => $this->normalizeValue($indexable?->schema_article_type ?? null),

                    // فعلاً ID مدیای لاراول نداریم؛ URL را نگه می‌داریم برای استفاده یا QA بعدی
                    'open_graph_image_url' => $this->normalizeValue($indexable?->open_graph_image ?? null),
                    'twitter_image_url' => $this->normalizeValue($indexable?->twitter_image ?? null),

                    'robots_noindex' => $indexable?->robots_noindex ?? null,
                    'robots_nofollow' => $indexable?->robots_nofollow ?? null,
                    'robots_advanced' => $this->normalizeValue($indexable?->robots_advanced ?? null),
                ],
            ],
        ];
    }

    private function buildRobots(?object $indexable): ?string
    {
        if (!$indexable) {
            return null;
        }

        $directives = [];

        $directives[] = ((int)($indexable->robots_noindex ?? 0)) === 1
            ? 'noindex'
            : 'index';

        $directives[] = ((int)($indexable->robots_nofollow ?? 0)) === 1
            ? 'nofollow'
            : 'follow';

        $advanced = $this->normalizeValue($indexable->robots_advanced ?? null);

        if ($advanced) {
            foreach (explode(',', $advanced) as $directive) {
                $directive = trim($directive);

                if ($directive !== '') {
                    $directives[] = $directive;
                }
            }
        } else {
            $directives[] = 'max-image-preview:large';
            $directives[] = 'max-snippet:-1';
            $directives[] = 'max-video-preview:-1';
        }

        return implode(', ', array_values(array_unique($directives)));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function isEmptyPayload(array $payload): bool
    {
        return blank($payload['focus_keyword'])
            && blank($payload['title'])
            && blank($payload['description'])
            && blank($payload['robots'])
            && blank($payload['canonical_url'])
            && blank($payload['og_title'])
            && blank($payload['og_description'])
            && blank($payload['x_title'])
            && blank($payload['x_description'])
            && blank($payload['schema_type'])
            && blank(data_get($payload, 'schema_settings.yoast.primary_category_wp_term_id'))
            && blank(data_get($payload, 'schema_settings.yoast.is_cornerstone'))
            && blank(data_get($payload, 'schema_settings.yoast.focus_keywords'))
            && blank(data_get($payload, 'schema_settings.yoast.keyword_synonyms'))
            && blank(data_get($payload, 'schema_settings.yoast.estimated_reading_time_minutes'));
    }

    private function firstFilled(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            $value = $this->normalizeValue($value);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function normalizeValue(mixed $value): ?string
    {
        $value = trim((string)$value);

        if ($value === '') {
            return null;
        }

        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function nullableInteger(?string $value): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return is_numeric($value) ? (int)$value : null;
    }

    private function toBoolean(?string $value): ?bool
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function decodeMaybeJson(?string $value): mixed
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }
}
