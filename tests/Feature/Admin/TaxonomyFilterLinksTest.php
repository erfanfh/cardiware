<?php

namespace Tests\Feature\Admin;

use App\Application\Content\Listings\PostListing;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Tests\TestCase;

class TaxonomyFilterLinksTest extends TestCase
{
    public function test_taxonomy_badges_link_to_the_existing_list_filters(): void
    {
        $expectedLinks = [
            'views/admin/content/posts/partials/list-table.blade.php' => [
                "route('admin.posts.index', ['category_ids' => [\$category->id]])",
                "route('admin.posts.index', ['tag_ids' => [\$tag->id]])",
            ],
            'views/admin/catalog/products/partials/list-table.blade.php' => [
                "route('admin.products.index', ['category_ids' => [\$category->id]])",
                "route('admin.products.index', ['tag_ids' => [\$tag->id]])",
            ],
            'views/admin/projects/projects/partials/list-table.blade.php' => [
                "route('admin.projects.index', ['tag_ids' => [\$tag->id]])",
            ],
            'views/admin/video/videos/partials/list-table.blade.php' => [
                "route('admin.videos.index', ['category_ids' => [\$category->id]])",
            ],
        ];

        foreach ($expectedLinks as $relativePath => $links) {
            $view = file_get_contents(resource_path($relativePath));

            self::assertIsString($view, $relativePath);

            foreach ($links as $link) {
                self::assertStringContainsString($link, $view, $relativePath);
            }
        }
    }

    public function test_post_category_filter_constrains_the_categories_relation(): void
    {
        $relationQuery = Mockery::mock(Builder::class);
        $relationQuery
            ->shouldReceive('whereIn')
            ->once()
            ->with('taxonomy_terms.id', [12])
            ->andReturnSelf();

        $query = Mockery::mock(Builder::class);
        $query
            ->shouldReceive('whereHas')
            ->once()
            ->with('categories', Mockery::on(function (callable $constraint) use ($relationQuery): bool {
                $constraint($relationQuery);

                return true;
            }))
            ->andReturnSelf();
        $query
            ->shouldReceive('orderBy')
            ->once()
            ->with('published_at', 'desc')
            ->andReturnSelf();

        app(PostListing::class)->handle($query, [
            'category_ids' => [12],
        ]);
    }
}
