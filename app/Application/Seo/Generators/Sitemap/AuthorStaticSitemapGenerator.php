<?php

namespace App\Application\Seo\Generators\Sitemap;

use App\Application\Seo\DTOs\Sitemap\SitemapUrlDTO;
use App\Application\Seo\Support\Sitemap\AbstractStaticSitemapGenerator;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuthorStaticSitemapGenerator extends AbstractStaticSitemapGenerator
{
    public function key(): string
    {
        return 'author';
    }

    protected function query(): Builder
    {
        return User::query()
            ->whereHas('posts', function (Builder $query) {
                $query->where('status', PostStatusEnum::ACTIVE);
            })
            ->select([
                'id',
                'username',
                'updated_at',
            ]);
    }

    protected function filenameForPage(int $page): string
    {
        return $page === 1
            ? 'author-sitemap.xml'
            : "author-sitemap{$page}.xml";
    }

    protected function sitemapUrlForPage(int $page): string
    {
        return $page === 1
            ? url('/author-sitemap.xml')
            : url("/author-sitemap{$page}.xml");
    }

    protected function mapModelToUrl(Model $model): SitemapUrlDTO
    {
        /** @var User $user */
        $user = $model;

        return new SitemapUrlDTO(
            url: front_route('author', $user),
            lastmod: $this->authorLastmod($user),
            images: [],
        );
    }

    private function authorLastmod(User $user): mixed
    {
        $lastmod = Post::query()
            ->where('status', PostStatusEnum::ACTIVE)
            ->where('author_id', $user->id)
            ->max('updated_at');

        return $lastmod
            ? Carbon::parse($lastmod)
            : $user->updated_at;
    }
}
