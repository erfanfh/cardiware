<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\Content\Actions\GetAboutUsPageDataAction;
use App\Application\Content\Actions\GetPageDetailDataAction;
use App\Application\Content\Actions\GetPostDetailDataAction;
use App\Application\Content\Actions\GetTeamPageDataAction;
use App\Application\Content\DTOs\ResolvedContentDTO;
use App\Application\Content\Services\PageTemplateService;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ContentController
{
    public function __construct(
        private readonly StoreViewAction          $storeViewAction,
        private readonly PageTemplateService      $pageTemplateService,
        private readonly GetAboutUsPageDataAction $getAboutUsPageDataAction,
        private readonly GetTeamPageDataAction    $getTeamPageDataAction,
        private readonly GetPostDetailDataAction  $getPostDetailDataAction,
        private readonly GetPageDetailDataAction  $getPageDetailDataAction,
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
        $page = $this->getPageDetailDataAction->execute($page);

        $viewName = 'front.pages.detail';

        if ($page->template && $this->pageTemplateService->exists($page->template)) {
            $viewName = "templates.{$page->template}";
        }

        $viewData = compact('page');

        if ($viewName === 'templates.about-us') {
            $viewData = array_merge(
                $viewData,
                $this->getAboutUsPageDataAction->execute(),
            );
        }

        if ($viewName === 'templates.team') {
            $viewData = array_merge(
                $viewData,
                $this->getTeamPageDataAction->execute(),
            );
        }

        return view($viewName, $viewData);
    }

    private function renderPost(Post $post): View
    {
        $data = $this->getPostDetailDataAction->execute($post);

        return view('front.posts.detail', [
            'layoutData' => $data['layoutData'],
            'post' => $data['post'],
            'features' => $data['features'],
            'relatedPosts' => $data['relatedPosts'],
        ]);
    }
}
