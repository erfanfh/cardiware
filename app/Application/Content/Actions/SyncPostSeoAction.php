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
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }
}
