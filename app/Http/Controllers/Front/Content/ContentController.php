<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\Content\Actions\ResolveContentBySlugAction;
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
            return view('front.posts.detail', [
                'post' => $resolvedContent->model,
            ]);
        }

        throw new NotFoundHttpException();
    }
}
