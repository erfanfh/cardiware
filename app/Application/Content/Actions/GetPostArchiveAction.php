<?php

namespace App\Application\Content\Actions;

use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetPostArchiveAction
{
    public function execute(int $page, int $perPage, ?int $authorId = null, ?TaxonomyTerm $term = null): LengthAwarePaginator
    {
        return app(PublicReadCache::class)->paginate(
            'post-archive', [$page, $perPage, $authorId, $term?->id],
            [Post::class, TaxonomyTerm::class, MediaFile::class],
            function () use ($page, $perPage, $authorId, $term) {
                $query = $term ? $term->postsIncludingDescendants() : Post::query();

                return $query
                    ->where('status', PostStatusEnum::ACTIVE)
                    ->where('published_at', '<=', now())
                    ->when($authorId, fn ($query) => $query->where('author_id', $authorId))
                    ->with(['picture.primaryVariant', 'picture.variants', 'categories'])
                    ->latest('published_at')
                    ->paginate($perPage, ['*'], 'page', $page);
            },
            [Post::class => ['published_at']],
        );
    }
}
