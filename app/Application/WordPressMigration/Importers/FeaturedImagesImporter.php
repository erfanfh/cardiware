<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Models\Post;
use App\Domain\Media\Models\MediaFile;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Facades\DB;
use Throwable;

class FeaturedImagesImporter
{
    private int $processed = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private int $failed = 0;

    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('postmeta')
            ->where('meta_key', '_thumbnail_id')
            ->orderBy('post_id')
            ->chunk(500, function ($rows) use ($onProgress) {
                foreach ($rows as $row) {
                    $this->processed++;

                    try {
                        $this->importFeaturedImage($row);
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('Featured image import failed', [
                            'post_id' => $row->post_id,
                            'meta_value' => $row->meta_value,
                            'message' => $exception->getMessage(),
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
        return DB::connection('wordpress')
            ->table('postmeta')
            ->where('meta_key', '_thumbnail_id')
            ->count();
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

    private function importFeaturedImage(object $row): void
    {
        $postMap = $this->findMap(
            sourceType: 'wordpress_post',
            sourceId: (int)$row->post_id,
            targetType: Post::class
        );

        if (!$postMap) {
            $this->skipped++;
            return;
        }

        $attachmentId = (int)$row->meta_value;

        if ($attachmentId <= 0) {
            $this->skipped++;
            return;
        }

        $attachmentMap = $this->findMap(
            sourceType: 'wordpress_attachment',
            sourceId: $attachmentId,
            targetType: MediaFile::class
        );

        if (!$attachmentMap) {
            $this->skipped++;
            return;
        }

        Post::withoutGlobalScopes()
            ->whereKey($postMap->target_id)
            ->update([
                'picture_id' => $attachmentMap->target_id,
            ]);

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
