<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Models\Post;
use App\Domain\Media\Models\MediaFile;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Facades\DB;

class FeaturedImagesImporter
{
    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('postmeta')
            ->where('meta_key', '_thumbnail_id')
            ->orderBy('post_id')
            ->chunk(500, function ($rows) use ($onProgress) {
                foreach ($rows as $row) {
                    $this->importFeaturedImage($row);

                    if ($onProgress) {
                        $onProgress();
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

    private function importFeaturedImage(object $row): void
    {
        $postMap = $this->findMap(
            sourceType: 'wordpress_post',
            sourceId: (int)$row->post_id,
            targetType: Post::class
        );

        if (!$postMap) {
            return;
        }

        $attachmentId = (int)$row->meta_value;

        if ($attachmentId <= 0) {
            return;
        }

        $attachmentMap = $this->findMap(
            sourceType: 'wordpress_attachment',
            sourceId: $attachmentId,
            targetType: MediaFile::class
        );

        if (!$attachmentMap) {
            return;
        }

        Post::withoutGlobalScopes()
            ->whereKey($postMap->target_id)
            ->update([
                'picture_id' => $attachmentMap->target_id,
            ]);
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
