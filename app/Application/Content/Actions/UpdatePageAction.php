<?php

namespace App\Application\Content\Actions;

use App\Application\Shared\Cache\Invalidators\ContentSlugCacheInvalidator;
use App\Application\Shared\Cache\Invalidators\PageCacheInvalidator;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Content\Models\Page;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdatePageAction
{
    public function __construct(
        protected AssignSlugAction            $assignSlugAction,
        protected ContentSlugCacheInvalidator $contentSlugCacheInvalidator,
        protected PageCacheInvalidator        $pageCacheInvalidator,
    )
    {
    }

    /**
     * @param Page $page
     * @param array $data
     * @return Page
     * @throws Throwable
     */
    public function execute(Page $page, array $data): Page
    {
        $data['title'] = $data['title'] ?: 'بدون عنوان';
        $data['body'] = $data['body'] ?: '';
        $data['slug'] = $data['slug'] ?: 'untitled-page';

        $page = DB::transaction(function () use ($page, $data) {
            $pageData = $data;

            unset(
                $pageData['slug'],
            );

            $page->update($pageData);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::CONTENT,
                    model: $page,
                    desiredSlug: $desiredSlug,
                )
            );

            return $page;
        });

        $this->contentSlugCacheInvalidator->forgetForModel($page);
        $this->pageCacheInvalidator->afterUpdated($page);

        return $page;
    }
}
