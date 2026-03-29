<?php

namespace App\Application\Content\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Taxonomy\Actions\SyncModelTagsAction;
use App\Domain\Content\Models\Post;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdatePostAction
{
    public function __construct(
        protected SyncModelTagsAction $syncModelTagsAction,
        protected AssignSlugAction    $assignSlugAction,
    )
    {
    }

    /**
     * @param Post $post
     * @param array $data
     * @return Post
     * @throws Throwable
     */
    public function execute(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $postData = $data;

            unset(
                $postData['tags'],
                $postData['slug'],
            );

            $post->update($postData);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::CONTENT,
                    model: $post,
                    desiredSlug: $desiredSlug,
                )
            );

            $this->syncModelTagsAction->execute($post, $data['tags'] ?? []);

            return $post;
        });
    }
}
