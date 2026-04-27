<?php

namespace App\Http\Controllers\Front\Seo;

use App\Application\Seo\Actions\Sitemap\BuildSitemapIndexAction;
use App\Application\Seo\Renderers\SitemapIndexXmlRenderer;
use App\Application\Seo\Renderers\SitemapXmlRenderer;
use App\Application\Seo\Support\Sitemap\SitemapProviderRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SitemapController extends Controller
{
    public function index(
        BuildSitemapIndexAction $action,
        SitemapIndexXmlRenderer $renderer,
    ): Response
    {
        return $renderer->render($action->execute());
    }

    public function posts(
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('post', 1, $registry, $renderer);
    }

    public function paginatedPosts(
        int                     $page,
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('post', $page, $registry, $renderer);
    }

    public function projects(
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('project', 1, $registry, $renderer);
    }

    public function paginatedProjects(
        int                     $page,
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('project', $page, $registry, $renderer);
    }

    public function pages(
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('page', 1, $registry, $renderer);
    }

    public function paginatedPages(
        int                     $page,
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('page', $page, $registry, $renderer);
    }

    public function categories(
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('category', 1, $registry, $renderer);
    }

    public function paginatedCategories(
        int                     $page,
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('category', $page, $registry, $renderer);
    }

    public function tags(
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('tag', 1, $registry, $renderer);
    }

    public function paginatedTags(
        int                     $page,
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('tag', $page, $registry, $renderer);
    }

    public function authors(
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('author', 1, $registry, $renderer);
    }

    public function paginatedAuthors(
        int                     $page,
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        return $this->show('author', $page, $registry, $renderer);
    }

    private function show(
        string                  $type,
        int                     $page,
        SitemapProviderRegistry $registry,
        SitemapXmlRenderer      $renderer,
    ): Response
    {
        $provider = $registry->findByType($type);

        if (!$provider || !$provider->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if ($page < 1 || !$provider->hasPage($page)) {
            throw new NotFoundHttpException();
        }

        return $renderer->render($provider->urls($page));
    }
}
