<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\System\Models\PageView;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class PageViewsImporter
{
    private int $processed = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private int $failed = 0;

    private const META_KEY = 'better-views-count';

    private const SUPPORTED_TYPES = [
        'post' => [
            'source_type' => 'wordpress_post',
            'target_type' => Post::class,
        ],
        'page' => [
            'source_type' => 'wordpress_page',
            'target_type' => Page::class,
        ],
    ];

    public function import(?callable $onProgress = null): void
    {
        $this->query()
            ->orderBy('postmeta.meta_id')
            ->chunkById(500, function ($rows) use ($onProgress) {
                foreach ($rows as $row) {
                    $this->processed++;

                    try {
                        $this->importPageView($row);
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('WordPress page view import failed', [
                            'wordpress_post_id' => $row->post_id,
                            'wordpress_post_type' => $row->post_type,
                            'meta_value' => $row->meta_value,
                            'message' => $exception->getMessage(),
                        ]);
                    }

                    if ($onProgress) {
                        $onProgress($this->stats());
                    }
                }
            }, 'postmeta.meta_id', 'meta_id');
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
            ->table('postmeta')
            ->join('posts', 'posts.ID', '=', 'postmeta.post_id')
            ->where('postmeta.meta_key', self::META_KEY)
            ->whereIn('posts.post_type', array_keys(self::SUPPORTED_TYPES))
            ->select([
                'postmeta.meta_id',
                'postmeta.post_id',
                'postmeta.meta_value',
                'posts.post_type',
            ]);
    }

    private function importPageView(object $row): void
    {
        $config = self::SUPPORTED_TYPES[$row->post_type] ?? null;

        if (!$config) {
            $this->skipped++;
            return;
        }

        $count = $this->normalizeCount($row->meta_value);

        if ($count <= 0) {
            $this->skipped++;
            return;
        }

        $map = $this->findMap(
            sourceType: $config['source_type'],
            sourceId: (int)$row->post_id,
            targetType: $config['target_type']
        );

        if (!$map) {
            $this->skipped++;
            return;
        }

        /** @var class-string<Model> $targetType */
        $targetType = $config['target_type'];

        PageView::query()->updateOrCreate(
            [
                'pageable_type' => $targetType,
                'pageable_id' => $map->target_id,
            ],
            [
                'count' => $count,
            ]
        );

        $this->updated++;
    }

    private function normalizeCount(mixed $value): int
    {
        $value = trim((string)$value);

        if ($value === '' || !is_numeric($value)) {
            return 0;
        }

        return max(0, (int)$value);
    }

    private function findMap(string $sourceType, int $sourceId, string $targetType): ?WpMigrationMap
    {
        return WpMigrationMap::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('target_type', $targetType)
            ->first();
    }
}
