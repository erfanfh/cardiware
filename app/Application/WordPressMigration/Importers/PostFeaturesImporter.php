<?php

namespace App\Application\WordPressMigration\Importers;

use App\Application\Content\Actions\SyncPostFeaturesAction;
use App\Domain\Content\Models\Post;
use App\Domain\Feature\Enums\FrontBadgeOptionEnum;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Facades\DB;
use Throwable;

class PostFeaturesImporter
{
    private int $processed = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private int $failed = 0;

    private const META_KEYS = [
        'updated_post_checkbox',
        'special_post_checkbox',
        'vip_post_checkbox',
        'post_category_label',
    ];

    public function __construct(
        protected SyncPostFeaturesAction $syncPostFeaturesAction,
    )
    {
    }

    public function import(?callable $onProgress = null): void
    {
        $this->query()
            ->orderBy('posts.ID')
            ->chunk(300, function ($rows) use ($onProgress) {
                foreach ($rows as $row) {
                    $this->processed++;

                    try {
                        $result = $this->importPostFeatures($row);

                        if ($result === 'updated') {
                            $this->updated++;
                        }

                        if ($result === 'skipped') {
                            $this->skipped++;
                        }
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('WordPress post features import failed', [
                            'wordpress_post_id' => $row->post_id,
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
            });
    }

    public function total(): int
    {
        return $this->query()->count();
    }

    public function stats(): array
    {
        return [
            'processed' => $this->processed,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
        ];
    }

    private function query()
    {
        return DB::connection('wordpress')
            ->table('posts')
            ->where('posts.post_type', 'post')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('postmeta')
                    ->whereColumn('postmeta.post_id', 'posts.ID')
                    ->whereIn('postmeta.meta_key', self::META_KEYS);
            })
            ->select([
                'posts.ID as post_id',
            ]);
    }

    private function importPostFeatures(object $row): string
    {
        $map = WpMigrationMap::query()
            ->where('source_type', 'wordpress_post')
            ->where('source_id', $row->post_id)
            ->where('target_type', Post::class)
            ->first();

        if (!$map) {
            logger()->warning('WordPress post features skipped because post map was not found', [
                'wordpress_post_id' => $row->post_id,
            ]);

            return 'skipped';
        }

        $post = Post::withoutGlobalScopes()->find($map->target_id);

        if (!$post) {
            logger()->warning('WordPress post features skipped because target Laravel post was not found', [
                'wordpress_post_id' => $row->post_id,
                'target_post_id' => $map->target_id,
            ]);

            return 'skipped';
        }

        $meta = $this->getPostMeta((int)$row->post_id);

        $this->syncPostFeaturesAction->execute($post, [
            'is_special' => $this->resolveCheckboxValue($meta['special_post_checkbox'] ?? null),
            'is_vip' => $this->resolveCheckboxValue($meta['vip_post_checkbox'] ?? null),
            'is_updated' => $this->resolveCheckboxValue($meta['updated_post_checkbox'] ?? null),
            'front_badge' => $this->normalizeFrontBadge($meta['post_category_label'] ?? null),
        ]);

        return 'updated';
    }

    /**
     * @return array<string, mixed>
     */
    private function getPostMeta(int $wpPostId): array
    {
        return DB::connection('wordpress')
            ->table('postmeta')
            ->where('post_id', $wpPostId)
            ->whereIn('meta_key', self::META_KEYS)
            ->pluck('meta_value', 'meta_key')
            ->all();
    }

    private function resolveCheckboxValue(mixed $value): bool
    {
        $value = trim((string)$value);

        if ($value === '') {
            return false;
        }

        return in_array(strtolower($value), [
            '1',
            'yes',
            'true',
            'on',
            'checked',
        ], true);
    }

    private function normalizeFrontBadge(mixed $value): ?string
    {
        $value = trim((string)$value);

        if ($value === '' || strtolower($value) === 'none') {
            return null;
        }

        $badge = FrontBadgeOptionEnum::tryFrom($value);

        if (!$badge) {
            logger()->warning('WordPress post feature front badge value is unsupported', [
                'value' => $value,
            ]);
        }

        return $badge?->value;
    }
}
