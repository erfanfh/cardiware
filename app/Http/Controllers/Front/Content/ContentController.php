<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\Content\Actions\GetPostFeaturesAction;
use App\Application\Content\Actions\ResolveContentBySlugAction;
use App\Application\Content\Services\PageTemplateService;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ContentController
{
    public function __invoke(
        string                     $slug,
        ResolveContentBySlugAction $resolveContentBySlugAction,
    ): View|RedirectResponse
    {
        $resolvedContent = $resolveContentBySlugAction->execute($slug);

        if ($resolvedContent->isNotFound()) {
            throw new NotFoundHttpException();
        }

        if ($resolvedContent->shouldRedirect()) {
            return redirect()->to($resolvedContent->redirectUrl, 301);
        }

        app(StoreViewAction::class)->execute($resolvedContent->model);

        if ($resolvedContent->isPage()) {
            /** @var Page $page */
            $page = $resolvedContent->model;

            $args = compact('page');
            $viewName = 'front.pages.detail';

            if ($page->template and app(PageTemplateService::class)->exists($page->template)) {
                $viewName = "templates.$page->template";
            }

            return view($viewName, $args);
        }

        if ($resolvedContent->isPost()) {
            /** @var Post $post */
            $post = Post::query()
                ->with([
                    'author:id,display_name',
                    'picture.primaryVariant',
                    'categories:id,title,path',
                    'tags:id,title,path',
                    'imageGallery.mediaFile',
                ])
                ->withCount('approvedComments')
                ->findOrFail($resolvedContent->model->id);

            $features = app(GetPostFeaturesAction::class)->execute($post);

            $relatedPosts = Post::where('id', '!=', $post->id);

            if (get_setting('general', 'select_related_content_settings') === 'tag') {
                $relatedPosts = $relatedPosts->whereHas('tags', function ($query) use ($post) {
                    $query->whereIn('taxonomy_terms.id', $post->tags->pluck('id'));
                });
            } elseif (get_setting('general', 'select_related_content_settings') === 'category') {
                $relatedPosts = $relatedPosts->whereHas('categories', function ($query) use ($post) {
                    $query->whereIn('taxonomy_terms.id', $post->categories->pluck('id'));
                });
            }

            $relatedPosts = $relatedPosts
                ->with(['picture.primaryVariant', 'categories:id,title,path'])
                ->inRandomOrder()
                ->take(3)
                ->get();

            return view('front.posts.detail', compact('post', 'features', 'relatedPosts'));
        }

        throw new NotFoundHttpException();
    }
}
