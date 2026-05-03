<?php

namespace App\Application\WordPressMigration\Importers;

use App\Application\Seo\Actions\SyncSeoMetaAction;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class YoastSeoTaxonomyMetaImporter
{
    private int $processed = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private int $missingMap = 0;
    private int $failed = 0;

    private const SUPPORTED_TAXONOMIES = [
        'category',
        'post_tag',
    ];

    private const META_KEYS = [
        '_yoast_wpseo_title',
        '_yoast_wpseo_metadesc',
        '_yoast_wpseo_focuskw',
        '_yoast_wpseo_canonical',
        '_yoast_wpseo_bctitle',
        '_yoast_wpseo_noindex',
        '_yoast_wpseo_opengraph-title',
        '_yoast_wpseo_opengraph-description',
        '_yoast_wpseo_opengraph-image',
        '_yoast_wpseo_twitter-title',
        '_yoast_wpseo_twitter-description',
        '_yoast_wpseo_twitter-image',
    ];

    public function __construct(
        private readonly SyncSeoMetaAction $syncSeoMetaAction,
    )
    {
    }

    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('term_taxonomy as tt')
            ->join('terms as t', 't.term_id', '=', 'tt.term_id')
            ->whereIn('tt.taxonomy', self::SUPPORTED_TAXONOMIES)
            ->orderBy('tt.term_id')
            ->select([
                't.term_id',
                't.name',
                't.slug',
                'tt.taxonomy',
                'tt.description',
            ])
            ->chunkById(200, function ($wpTerms) use ($onProgress) {
                foreach ($wpTerms as $wpTerm) {
                    $this->processed++;

                    try {
                        $result = $this->importSeoMeta($wpTerm);

                        match ($result) {
                            'updated' => $this->updated++,
                            'skipped' => $this->skipped++,
                            'missing_map' => $this->missingMap++,
                        };
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('Yoast taxonomy SEO meta import failed', [
                            'wordpress_term_id' => $wpTerm->term_id,
                            'taxonomy' => $wpTerm->taxonomy,
                            'term_name' => $wpTerm->name ?? null,
                            'term_slug' => $wpTerm->slug ?? null,
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
            }, 'tt.term_id', 'term_id');
    }

    public function total(): int
    {
        return DB::connection('wordpress')
            ->table('term_taxonomy')
            ->whereIn('taxonomy', self::SUPPORTED_TAXONOMIES)
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

    private function importSeoMeta(object $wpTerm): string
    {
        $seoable = $this->resolveSeoable($wpTerm);

        if (!$seoable) {
            logger()->warning('Yoast taxonomy SEO meta skipped because target seoable map/model was not found', [
                'wordpress_term_id' => $wpTerm->term_id,
                'taxonomy' => $wpTerm->taxonomy,
                'term_name' => $wpTerm->name ?? null,
                'term_slug' => $wpTerm->slug ?? null,
            ]);

            return 'missing_map';
        }

        $meta = $this->getYoastTermMeta((int)$wpTerm->term_id);
        $indexable = $this->getYoastIndexable((int)$wpTerm->term_id, (string)$wpTerm->taxonomy);

        if ($meta === [] && !$indexable) {
            logger()->warning('Yoast taxonomy SEO meta skipped because no Yoast meta or indexable data was found', [
                'wordpress_term_id' => $wpTerm->term_id,
                'taxonomy' => $wpTerm->taxonomy,
            ]);

            return 'skipped';
        }

        $payload = $this->buildSeoPayload($wpTerm, $meta, $indexable);

        if ($this->isEmptyPayload($payload)) {
            logger()->warning('Yoast taxonomy SEO meta skipped because payload is empty', [
                'wordpress_term_id' => $wpTerm->term_id,
                'taxonomy' => $wpTerm->taxonomy,
                'has_meta' => $meta !== [],
                'has_indexable' => $indexable !== null,
            ]);

            return 'skipped';
        }

        $this->syncSeoMetaAction->execute($seoable, $payload);

        return 'updated';
    }

    private function resolveSeoable(object $wpTerm): ?Model
    {
        $sourceType = match ($wpTerm->taxonomy) {
            'category' => 'wordpress_category',
            'post_tag' => 'wordpress_tag',
            default => null,
        };

        if (!$sourceType) {
            return null;
        }

        $map = WpMigrationMap::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $wpTerm->term_id)
            ->where('target_type', TaxonomyTerm::class)
            ->first();

        if (!$map) {
            return null;
        }

        return TaxonomyTerm::withoutGlobalScopes()->find($map->target_id);
    }

    /**
     * @return array<string, string|null>
     */
    private function getYoastTermMeta(int $wpTermId): array
    {
        return DB::connection('wordpress')
            ->table('termmeta')
            ->where('term_id', $wpTermId)
            ->whereIn('meta_key', self::META_KEYS)
            ->pluck('meta_value', 'meta_key')
            ->mapWithKeys(fn($value, $key) => [$key => $this->normalizeValue($value)])
            ->all();
    }

    private function getYoastIndexable(int $wpTermId, string $taxonomy): ?object
    {
        return DB::connection('wordpress')
            ->table('yoast_indexable')
            ->where('object_id', $wpTermId)
            ->where('object_type', 'term')
            ->where('object_sub_type', $taxonomy)
            ->first();
    }

    /**
     * @param array<string, string|null> $meta
     * @return array<string, mixed>
     */
    private function buildSeoPayload(object $wpTerm, array $meta, ?object $indexable): array
    {
        return [
            'focus_keyword' => $this->firstFilled(
                $meta['_yoast_wpseo_focuskw'] ?? null,
                $indexable?->primary_focus_keyword ?? null,
            ),

            'title' => $this->firstFilled(
                $indexable?->title ?? null,
                $meta['_yoast_wpseo_title'] ?? null,
            ),

            'description' => $this->firstFilled(
                $indexable?->description ?? null,
                $meta['_yoast_wpseo_metadesc'] ?? null,
            ),

            'robots' => $this->buildRobots($meta, $indexable),

            'canonical_url' => $this->firstFilled(
                $indexable?->canonical ?? null,
                $meta['_yoast_wpseo_canonical'] ?? null,
            ),

            'og_title' => $this->firstFilled(
                $indexable?->open_graph_title ?? null,
                $meta['_yoast_wpseo_opengraph-title'] ?? null,
            ),

            'og_description' => $this->firstFilled(
                $indexable?->open_graph_description ?? null,
                $meta['_yoast_wpseo_opengraph-description'] ?? null,
            ),

            'x_title' => $this->firstFilled(
                $indexable?->twitter_title ?? null,
                $meta['_yoast_wpseo_twitter-title'] ?? null,
            ),

            'x_description' => $this->firstFilled(
                $indexable?->twitter_description ?? null,
                $meta['_yoast_wpseo_twitter-description'] ?? null,
            ),

            'schema_type' => $this->normalizeValue($indexable?->schema_page_type ?? null),

            'schema_settings' => [
                'yoast' => [
                    'indexable_id' => $indexable?->id ?? null,

                    'wordpress_term_id' => (int)$wpTerm->term_id,
                    'taxonomy' => $this->normalizeValue($wpTerm->taxonomy ?? null),
                    'term_name' => $this->normalizeValue($wpTerm->name ?? null),
                    'term_slug' => $this->normalizeValue($wpTerm->slug ?? null),

                    'permalink' => $this->normalizeValue($indexable?->permalink ?? null),

                    'breadcrumb_title' => $this->firstFilled(
                        $indexable?->breadcrumb_title ?? null,
                        $meta['_yoast_wpseo_bctitle'] ?? null,
                    ),

                    'open_graph_image_url' => $this->firstFilled(
                        $indexable?->open_graph_image ?? null,
                        $meta['_yoast_wpseo_opengraph-image'] ?? null,
                    ),

                    'twitter_image_url' => $this->firstFilled(
                        $indexable?->twitter_image ?? null,
                        $meta['_yoast_wpseo_twitter-image'] ?? null,
                    ),

                    'robots_noindex' => $indexable?->robots_noindex ?? $this->normalizeValue($meta['_yoast_wpseo_noindex'] ?? null),
                    'robots_nofollow' => $indexable?->robots_nofollow ?? null,
                    'robots_advanced' => $this->normalizeValue($indexable?->robots_advanced ?? null),

                    'schema_page_type' => $this->normalizeValue($indexable?->schema_page_type ?? null),
                ],
            ],
        ];
    }

    /**
     * @param array<string, string|null> $meta
     */
    private function buildRobots(array $meta, ?object $indexable): ?string
    {
        if (!$indexable && !array_key_exists('_yoast_wpseo_noindex', $meta)) {
            return null;
        }

        $directives = [];

        $noindex = $indexable
            ? ((int)($indexable->robots_noindex ?? 0)) === 1
            : $this->isYoastNoindexValue($meta['_yoast_wpseo_noindex'] ?? null);

        $directives[] = $noindex ? 'noindex' : 'index';

        $directives[] = $indexable && ((int)($indexable->robots_nofollow ?? 0)) === 1
            ? 'nofollow'
            : 'follow';

        $advanced = $this->normalizeValue($indexable?->robots_advanced ?? null);

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
            && blank(data_get($payload, 'schema_settings.yoast.breadcrumb_title'))
            && blank(data_get($payload, 'schema_settings.yoast.open_graph_image_url'))
            && blank(data_get($payload, 'schema_settings.yoast.twitter_image_url'));
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

    private function isYoastNoindexValue(?string $value): bool
    {
        $value = $this->normalizeValue($value);

        return in_array($value, ['1', 'true', 'yes', 'on', 'noindex'], true);
    }
}
