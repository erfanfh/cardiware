<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Media\Models\MediaFile;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class FeaturedImagesImporter
{
    private int $processed = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private int $failed = 0;

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
            ->orderBy('postmeta.post_id')
            ->chunk(500, function ($rows) use ($onProgress) {
                foreach ($rows as $row) {
                    $this->processed++;

                    try {
                        $this->importFeaturedImage($row);
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('WordPress featured image import failed', [
                            'wordpress_post_id' => $row->post_id,
                            'wordpress_post_type' => $row->post_type,
                            'thumbnail_id' => $row->meta_value,
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
            ->table('postmeta')
            ->join('posts', 'posts.ID', '=', 'postmeta.post_id')
            ->where('postmeta.meta_key', '_thumbnail_id')
            ->whereIn('posts.post_type', array_keys(self::SUPPORTED_TYPES))
            ->select([
                'postmeta.post_id',
                'postmeta.meta_value',
                'posts.post_type',
            ]);
    }

    private function importFeaturedImage(object $row): void
    {
        $config = self::SUPPORTED_TYPES[$row->post_type] ?? null;

        if (!$config) {
            $this->skipped++;
            return;
        }

        $contentMap = $this->findMap(
            sourceType: $config['source_type'],
            sourceId: (int)$row->post_id,
            targetType: $config['target_type']
        );

        if (!$contentMap) {
            $this->skipped++;

            logger()->warning('WordPress featured image skipped because content map was not found', [
                'wordpress_post_id' => $row->post_id,
                'wordpress_post_type' => $row->post_type,
                'thumbnail_id' => $row->meta_value,
            ]);

            return;
        }

        $attachmentId = (int)$row->meta_value;

        if ($attachmentId <= 0) {
            $this->skipped++;

            logger()->warning('WordPress featured image skipped because thumbnail id is invalid', [
                'wordpress_post_id' => $row->post_id,
                'wordpress_post_type' => $row->post_type,
                'thumbnail_id' => $row->meta_value,
            ]);

            return;
        }

        $attachmentMap = $this->findMap(
            sourceType: 'wordpress_attachment',
            sourceId: $attachmentId,
            targetType: MediaFile::class
        );

        if (!$attachmentMap) {
            $this->skipped++;

            logger()->warning('WordPress featured image skipped because attachment map was not found', [
                'wordpress_post_id' => $row->post_id,
                'wordpress_post_type' => $row->post_type,
                'thumbnail_id' => $attachmentId,
            ]);

            return;
        }

        /** @var class-string<Model> $targetType */
        $targetType = $config['target_type'];

        $updated = $targetType::withoutGlobalScopes()
            ->whereKey($contentMap->target_id)
            ->update([
                'picture_id' => $attachmentMap->target_id,
            ]);

        if ($updated === 0) {
            $this->skipped++;

            logger()->warning('WordPress featured image skipped because target content was not found', [
                'wordpress_post_id' => $row->post_id,
                'wordpress_post_type' => $row->post_type,
                'target_id' => $contentMap->target_id,
                'thumbnail_id' => $attachmentId,
                'media_file_id' => $attachmentMap->target_id,
            ]);

            return;
        }

        $this->updated++;
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
