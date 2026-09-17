<?php

namespace Tests\Unit\Preview;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Scopes\ActiveProductScope;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Scopes\ActivePageScope;
use App\Domain\Content\Scopes\ActivePostScope;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Scopes\ActiveProjectScope;
use App\Domain\Video\Models\Video;
use App\Domain\Video\Scopes\ActiveVideoScope;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PreviewScopeIsolationTest extends TestCase
{
    public function test_models_only_require_preview_when_the_public_route_cannot_show_them(): void
    {
        Carbon::setTestNow('2026-09-17 12:00:00');

        try {
            foreach ([Product::class, Post::class, Page::class, Video::class] as $model) {
                self::assertTrue((new $model([
                    'status' => 'active',
                    'published_at' => now()->subMinute(),
                ]))->isPubliclyVisible());

                self::assertFalse((new $model([
                    'status' => 'active',
                    'published_at' => now()->addMinute(),
                ]))->isPubliclyVisible());

                self::assertFalse((new $model([
                    'status' => 'inactive',
                    'published_at' => now()->subMinute(),
                ]))->isPubliclyVisible());
            }

            self::assertTrue((new Project(['status' => 'active']))->isPubliclyVisible());
            self::assertFalse((new Project(['status' => 'inactive']))->isPubliclyVisible());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_preview_removes_only_the_publication_scope_and_keeps_soft_delete_protection(): void
    {
        $request = Request::create('/preview/test');
        $route = (new Route('GET', '/preview/test', fn () => null))
            ->name('preview.test');
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);

        $models = [
            Product::class => ActiveProductScope::class,
            Post::class => ActivePostScope::class,
            Video::class => ActiveVideoScope::class,
            Page::class => ActivePageScope::class,
            Project::class => ActiveProjectScope::class,
        ];

        foreach ($models as $model => $activeScope) {
            $publicSql = $model::query()->toSql();
            $previewSql = $model::query()
                ->withoutGlobalScope($activeScope)
                ->toSql();

            self::assertStringContainsString('status', $publicSql);
            self::assertStringContainsString('deleted_at', $publicSql);
            self::assertStringNotContainsString('status', $previewSql);
            self::assertStringContainsString('deleted_at', $previewSql);
        }
    }
}
