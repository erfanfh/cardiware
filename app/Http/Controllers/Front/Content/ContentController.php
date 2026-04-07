<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\Content\Actions\GetPostFeaturesAction;
use App\Application\Content\Actions\ResolveContentBySlugAction;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
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
            return view('front.pages.detail', [
                'page' => $resolvedContent->model,
            ]);
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

            if (get_setting('general', 'select_related_content_settings') == 'tag') {
                $relatedPosts = Post::where('id', '!=', $post->id)
                ->whereHas('tags', function ($query) use ($post) {
                    $query->whereIn('taxonomy_terms.id', $post->tags->pluck('id'));
                })
                    ->take(3)
                    ->get();
            }
            elseif (get_setting('general', 'select_related_content_settings') == 'category') {
                $relatedPosts = Post::where('id', '!=', $post->id)
                    ->whereHas('categories', function ($query) use ($post) {
                        $query->whereIn('taxonomy_terms.id', $post->categories->pluck('id'));
                    })
                    ->take(3)
                    ->get();
            }
            else
                $relatedPosts = collect();

            return view('front.posts.detail', compact('post', 'features', 'relatedPosts'));
        }

        throw new NotFoundHttpException();
    }
}
