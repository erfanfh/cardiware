<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Models\Post;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Facades\DB;
use Throwable;

class PinnedPostsImporter
{
    private int $processed = 0;
    private int $pinned = 0;
    private int $missingMap = 0;
    private int $failed = 0;

    public function import(?callable $onProgress = null): void
    {
        $stickyPostIds = $this->getStickyPostIds();

        // اول همه پین‌ها را پاک می‌کنیم تا وضعیت دقیقاً مثل وردپرس شود.
        Post::withoutGlobalScopes()->update([
            'is_pinned' => false,
        ]);

        foreach ($stickyPostIds as $wpPostId) {
            $this->processed++;

            try {
                $map = WpMigrationMap::query()
                    ->where('source_type', 'wordpress_post')
                    ->where('source_id', $wpPostId)
                    ->where('target_type', Post::class)
                    ->first();

                if (!$map) {
                    $this->missingMap++;

                    logger()->warning('Pinned WordPress post map not found', [
                        'wordpress_post_id' => $wpPostId,
                    ]);

                    continue;
                }

                $updated = Post::withoutGlobalScopes()
                    ->where('id', $map->target_id)
                    ->update([
                        'is_pinned' => true,
                    ]);

                if ($updated > 0) {
                    $this->pinned++;
                }
            } catch (Throwable $exception) {
                $this->failed++;

                logger()->error('Pinned WordPress post import failed', [
                    'wordpress_post_id' => $wpPostId,
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
    }

    public function total(): int
    {
        return count($this->getStickyPostIds());
    }

    public function stats(): array
    {
        return [
            'processed' => $this->processed,
            'pinned' => $this->pinned,
            'missing_map' => $this->missingMap,
            'failed' => $this->failed,
        ];
    }

    private function getStickyPostIds(): array
    {
        $value = DB::connection('wordpress')
            ->table('options')
            ->where('option_name', 'sticky_posts')
            ->value('option_value');

        if (!$value) {
            return [];
        }

        $ids = @unserialize($value);

        if (!is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->map(fn($id) => (int)$id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
