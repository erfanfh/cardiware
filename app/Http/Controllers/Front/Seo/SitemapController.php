<?php

namespace App\Http\Controllers\Front\Seo;

use App\Application\Seo\Support\Sitemap\SitemapStorage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        private readonly SitemapStorage $storage,
    )
    {
    }

    public function index(): Response
    {
        return $this->xml('sitemap_index.xml');
    }

    public function posts(): Response
    {
        return $this->xml('post-sitemap.xml');
    }

    public function paginatedPosts(int $page): Response
    {
        return $this->xml("post-sitemap{$page}.xml");
    }

    public function projects(): Response
    {
        return $this->xml('project-sitemap.xml');
    }

    public function paginatedProjects(int $page): Response
    {
        return $this->xml("project-sitemap{$page}.xml");
    }

    public function pages(): Response
    {
        return $this->xml('page-sitemap.xml');
    }

    public function paginatedPages(int $page): Response
    {
        return $this->xml("page-sitemap{$page}.xml");
    }

    public function categories(): Response
    {
        return $this->xml('category-sitemap.xml');
    }

    public function paginatedCategories(int $page): Response
    {
        return $this->xml("category-sitemap{$page}.xml");
    }

    public function tags(): Response
    {
        return $this->xml('post_tag-sitemap.xml');
    }

    public function paginatedTags(int $page): Response
    {
        return $this->xml("post_tag-sitemap{$page}.xml");
    }

    public function authors(): Response
    {
        return $this->xml('author-sitemap.xml');
    }

    public function paginatedAuthors(int $page): Response
    {
        return $this->xml("author-sitemap{$page}.xml");
    }

    private function xml(string $filename): Response
    {
        abort_unless($this->storage->exists($filename), 404);

        return response($this->storage->get($filename), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
