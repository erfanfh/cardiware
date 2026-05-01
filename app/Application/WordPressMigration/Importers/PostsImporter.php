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
                $this->syncSlugs($post, $wpPost);
            }

            return 'skipped';
        }

        $authorId = $this->resolveAuthorId((int)$wpPost->post_author);

        $slug = $this->makeUniqueSlug(
            $wpPost->post_name ?: Str::slug($wpPost->post_title) ?: 'post-' . $wpPost->ID
        );

        $post = Post::withoutGlobalScopes()->create([
            'author_id' => $authorId,
            'title' => $this->cleanText($wpPost->post_title) ?: 'Untitled Post ' . $wpPost->ID,
            'slug' => $slug,
            'excerpt' => $wpPost->post_excerpt ?: null,
            'body' => $wpPost->post_content ?: '',
            'status' => $this->mapStatus($wpPost->post_status),
            'is_pinned' => false,
            'picture_id' => null,
            'published_at' => $this->normalizeDate($wpPost->post_date),
            'created_at' => $this->normalizeDate($wpPost->post_date) ?? now(),
            'updated_at' => $this->normalizeDate($wpPost->post_modified) ?? now(),
        ]);

        $this->syncSlugs($post, $wpPost);

        WpMigrationMap::query()->create([
            'source_type' => 'wordpress_post',
            'source_id' => $wpPost->ID,
            'target_type' => Post::class,
            'target_id' => $post->id,
            'meta' => [
                'post_name' => $wpPost->post_name,
                'post_status' => $wpPost->post_status,
                'post_type' => $wpPost->post_type,
                'post_author' => $wpPost->post_author,
                'guid' => $wpPost->guid,
            ],
        ]);

        return 'created';
    }

    private function syncSlugs(Post $post, object $wpPost): void
    {
        $this->ensureSlug($post, $post->slug, true);

        $oldSlugs = $this->getOldSlugs((int)$wpPost->ID);

        foreach ($oldSlugs as $oldSlug) {
            if ($oldSlug === $post->slug) {
                continue;
            }

            $this->ensureSlug($post, $oldSlug, false);
        }
    }

    private function ensureSlug(Post $post, string $slug, bool $isCurrent): void
    {
        $slug = trim($slug);

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
            ->filter()
            ->map(fn($slug) => trim((string)$slug))
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
        $base = $slug ?: 'post';
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
        } catch (\Throwable $e) {
            return Carbon::create(2000, 1, 1);
        }
    }
}
