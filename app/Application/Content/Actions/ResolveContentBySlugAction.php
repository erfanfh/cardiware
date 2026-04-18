<?php

namespace App\Application\Content\Actions;

use App\Application\Content\DTOs\ResolvedContentDTO;
use App\Application\Content\Services\ContentSlugCacheService;
use App\Application\Content\Services\ReservedSlugService;
use App\Domain\Content\Enums\ResolvedContentTypeEnum;
use App\Domain\Content\Models\ContentSlug;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use Illuminate\Support\Facades\Cache;

final class ResolveContentBySlugAction
{
    public function __construct(
        private readonly ReservedSlugService     $reservedSlugService,
        private readonly ContentSlugCacheService $contentSlugCacheService,
    )
    {
    }

    public function execute(string $slug): ResolvedContentDTO
    {
        $slug = trim($slug);

        if ($slug === '' || $this->reservedSlugService->isReserved($slug)) {
            return ResolvedContentDTO::notFound();
        }

        $cacheKey = $this->contentSlugCacheService->getCacheKey($slug);

        /** @var array<string, mixed> $cached */
        $cached = Cache::rememberForever($cacheKey, function () use ($slug): array {
            return $this->resolveFromDatabase($slug);
        });

        return $this->mapCachedResultToDTO($cached);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveFromDatabase(string $slug): array
    {
        $contentSlug = ContentSlug::query()
            ->with('sluggable')
            ->where('slug', $slug)
            ->first();

        if (!$contentSlug) {
            return [
                'type' => ResolvedContentTypeEnum::NOT_FOUND->value,
            ];
        }

        $model = $contentSlug->sluggable;

        if (!$model || !$this->isSupportedModel($model) || !$model->isPubliclyVisible()) {
            return [
                'type' => ResolvedContentTypeEnum::NOT_FOUND->value,
            ];
        }

        if (!$contentSlug->is_current) {
            return [
                'type' => ResolvedContentTypeEnum::REDIRECT->value,
                'slug' => $model->slug,
            ];
        }

        if ($model instanceof Page) {
            return [
                'type' => ResolvedContentTypeEnum::PAGE->value,
                'id' => $model->getKey(),
            ];
        }

        if ($model instanceof Post) {
            return [
                'type' => ResolvedContentTypeEnum::POST->value,
                'id' => $model->getKey(),
            ];
        }

        return [
            'type' => ResolvedContentTypeEnum::NOT_FOUND->value,
        ];
    }

    /**
     * @param array<string, mixed> $cached
     */
    private function mapCachedResultToDTO(array $cached): ResolvedContentDTO
    {
        $type = ResolvedContentTypeEnum::tryFrom(
            (string)($cached['type'] ?? ResolvedContentTypeEnum::NOT_FOUND->value)
        );

        return match ($type) {
            ResolvedContentTypeEnum::PAGE => $this->mapPageResult($cached),
            ResolvedContentTypeEnum::POST => $this->mapPostResult($cached),
            ResolvedContentTypeEnum::REDIRECT => $this->mapRedirectResult($cached),
            default => ResolvedContentDTO::notFound(),
        };
    }

    /**
     * @param array<string, mixed> $cached
     */
    private function mapPageResult(array $cached): ResolvedContentDTO
    {
        $pageId = $cached['id'] ?? null;

        if (!is_numeric($pageId)) {
            return ResolvedContentDTO::notFound();
        }

        $page = Page::query()->find((int)$pageId);

        if (!$page || !$page->isPubliclyVisible()) {
            return ResolvedContentDTO::notFound();
        }

        return ResolvedContentDTO::page($page);
    }

    /**
     * @param array<string, mixed> $cached
     */
    private function mapPostResult(array $cached): ResolvedContentDTO
    {
        $postId = $cached['id'] ?? null;

        if (!is_numeric($postId)) {
            return ResolvedContentDTO::notFound();
        }

        $post = Post::query()->find((int)$postId);

        if (!$post || !$post->isPubliclyVisible()) {
            return ResolvedContentDTO::notFound();
        }

        return ResolvedContentDTO::post($post);
    }

    /**
     * @param array<string, mixed> $cached
     */
    private function mapRedirectResult(array $cached): ResolvedContentDTO
    {
        $slug = trim((string)($cached['slug'] ?? ''));

        if ($slug === '') {
            return ResolvedContentDTO::notFound();
        }

        return ResolvedContentDTO::redirect('/' . $slug);
    }

    private function isSupportedModel(mixed $model): bool
    {
        return $model instanceof Page || $model instanceof Post;
    }
}
