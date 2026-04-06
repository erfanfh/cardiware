<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\Content\Actions\GetPostFeaturesAction;
use App\Application\Content\Actions\ResolveContentBySlugAction;
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
                    'categories:id,title',
                    'tags:id,title',
                    'imageGallery.mediaFile',
                ])
                ->withCount('approvedComments')
                ->findOrFail($resolvedContent->model->id);

            $features = app(GetPostFeaturesAction::class)->execute($post);

            return view('front.posts.detail', compact('post', 'features'));
        }

        throw new NotFoundHttpException();
    }
}
