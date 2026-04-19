<?php

namespace App\Application\Content\Actions;

use App\Domain\Content\Models\Post;

class SyncPostSeoAction
{
    /**
     * @param Post $post
     * @param array<string, mixed> $data
     * @return void
     */
    public function execute(Post $post, array $data): void
    {
        $post->seoMeta()->updateOrCreate([], [
            'focus_keyword' => $data['focus_keyword'] ?? null,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'robots' => $data['robots'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,

            'og_title' => $data['og_title'] ?? null,
            'og_description' => $data['og_description'] ?? null,
            'og_image_id' => data_get($data, 'og_image.id'),

            'x_title' => $data['x_title'] ?? null,
            'x_description' => $data['x_description'] ?? null,
            'x_image_id' => data_get($data, 'x_image.id'),

            'schema_type' => $data['schema_type'] ?? null,
            'schema_settings' => $data['schema_settings'] ?? null,
        ]);
    }
}
