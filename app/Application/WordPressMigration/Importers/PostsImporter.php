<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostsImporter
{
    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'post')
            ->orderBy('ID')
            ->chunk(200, function ($posts) use ($onProgress) {
                foreach ($posts as $wpPost) {
                    $this->importPost($wpPost);

                    if ($onProgress) {
                        $onProgress();
                    }
                }
            });
    }

    private function importPost(object $wpPost): void
    {
        $exists = WpMigrationMap::query()
            ->where('source_type', 'wordpress_post')
            ->where('source_id', $wpPost->ID)
            ->where('target_type', Post::class)
            ->exists();

        if ($exists) {
            return;
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
            'body' => $wpPost->post_content ?: null,
            'status' => $this->mapStatus($wpPost->post_status),
            'is_pinned' => false,
            'picture_id' => null,
            'published_at' => $this->normalizeDate($wpPost->post_date),
            'created_at' => $this->normalizeDate($wpPost->post_date) ?? now(),
            'updated_at' => $this->normalizeDate($wpPost->post_modified) ?? now(),
        ]);

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

    private function normalizeDate(?string $date): ?Carbon
    {
        if (!$date || str_starts_with($date, '0000-00-00')) {
            return null;
        }

        return Carbon::parse($date);
    }
}
