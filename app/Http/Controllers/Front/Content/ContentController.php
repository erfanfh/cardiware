<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\Content\Actions\GetPostFeaturesAction;
use App\Application\Content\DTOs\ResolvedContentDTO;
use App\Application\Content\Services\PageTemplateService;
use App\Application\Layout\Actions\GetPageLayoutRenderDataAction;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ContentController
{
    public function __construct(
        private readonly StoreViewAction               $storeViewAction,
        private readonly PageTemplateService           $pageTemplateService,
        private readonly GetPostFeaturesAction         $getPostFeaturesAction,
        private readonly GetPageLayoutRenderDataAction $getLayoutRenderDataAction,

    )
    {
    }

    public function __invoke(Request $request): View|RedirectResponse
    {
        $resolvedContent = $request->route('slug');

        if (!$resolvedContent instanceof ResolvedContentDTO) {
            throw new NotFoundHttpException();
        }

        if ($resolvedContent->isNotFound()) {
            throw new NotFoundHttpException();
        }

        if ($resolvedContent->shouldRedirect()) {
            return redirect()->to($resolvedContent->redirectUrl, 301);
        }

        if ($resolvedContent->isPage()) {
            $page = $resolvedContent->getPage();

            if (!$page instanceof Page) {
                throw new NotFoundHttpException();
            }

            $this->storeViewAction->execute($page);

            return $this->renderPage($page);
        }

        if ($resolvedContent->isPost()) {
            $post = $resolvedContent->getPost();

            if (!$post instanceof Post) {
                throw new NotFoundHttpException();
            }

            $this->storeViewAction->execute($post);

            return $this->renderPost($post);
        }

        throw new NotFoundHttpException();
    }

    private function renderPage(Page $page): View
    {
        $viewName = 'front.pages.detail';

        if ($page->template && $this->pageTemplateService->exists($page->template)) {
            $viewName = "templates.{$page->template}";
        }
        return view($viewName, compact('page'));
    }

    private function renderPost(Post $resolvedPost): View
    {
        $layoutData = $this->getLayoutRenderDataAction->execute('post-content');
        $post = $this->loadPostForDetail($resolvedPost);

        $features = $this->getPostFeaturesAction->execute($post);

        $relatedPosts = $this->resolveRelatedPosts($post);

        return view('front.posts.detail', compact('layoutData', 'post', 'features', 'relatedPosts'));
    }

    private function loadPostForDetail(Post $post): Post
    {
        $post->load([
            'author:id,username,display_name',
            'picture.primaryVariant',
            'categories:id,title,path',
            'tags:id,title,path',
            'imageGallery.mediaFile',
        ]);

        $post->loadCount('approvedComments');

        return $post;
    }

    private function resolveRelatedPosts(Post $post): Collection
    {
        $selectionMode = get_setting('general', 'select_related_content_settings');

        $query = Post::query()
            ->whereKeyNot($post->getKey());

        if ($selectionMode === 'tag') {
            $tagIds = $post->tags->pluck('id')->filter()->values();

            if ($tagIds->isNotEmpty()) {
                $query->whereHas('tags', function ($builder) use ($tagIds) {
                    $builder->whereIn('taxonomy_terms.id', $tagIds);
                });
            }
        } elseif ($selectionMode === 'category') {
            $categoryIds = $post->categories->pluck('id')->filter()->values();

            if ($categoryIds->isNotEmpty()) {
                $query->whereHas('categories', function ($builder) use ($categoryIds) {
                    $builder->whereIn('taxonomy_terms.id', $categoryIds);
                });
            }
        }

        return $query
            ->with([
                'picture.primaryVariant',
                'categories:id,title,path',
            ])
            ->inRandomOrder()
            ->take(3)
            ->get();
    }
}
