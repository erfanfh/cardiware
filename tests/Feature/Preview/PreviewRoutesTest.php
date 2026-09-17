<?php

namespace Tests\Feature\Preview;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\ProtectContentPreview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PreviewRoutesTest extends TestCase
{
    public function test_content_preview_routes_are_registered_and_admin_protected(): void
    {
        $routes = [
            'preview.products' => 'preview/products/{product}',
            'preview.posts' => 'preview/posts/{post}',
            'preview.videos' => 'preview/videos/{video}',
            'preview.pages' => 'preview/pages/{page}',
            'preview.projects' => 'preview/projects/{project}',
        ];

        foreach ($routes as $name => $uri) {
            $route = Route::getRoutes()->getByName($name);

            self::assertNotNull($route, "Missing preview route: {$name}");
            self::assertSame($uri, $route->uri());
            self::assertContains('web', $route->gatherMiddleware());
            self::assertContains('auth:web', $route->gatherMiddleware());
            self::assertContains(EnsureUserIsAdmin::class, $route->gatherMiddleware());
            self::assertContains(ProtectContentPreview::class, $route->gatherMiddleware());
            self::assertStringStartsWith('preview.', $route->getName());
            self::assertFalse(str_starts_with($route->getName(), 'admin.'));
        }
    }

    public function test_guest_cannot_access_content_preview_routes(): void
    {
        foreach ([
            'preview.products',
            'preview.posts',
            'preview.videos',
            'preview.pages',
            'preview.projects',
        ] as $routeName) {
            $this->get(route($routeName, 1))
                ->assertRedirect();
        }
    }

    public function test_preview_responses_cannot_be_cached_or_indexed(): void
    {
        $response = app(ProtectContentPreview::class)->handle(
            Request::create('/preview/test'),
            fn () => response('preview'),
        );

        self::assertSame(
            'max-age=0, must-revalidate, no-cache, no-store, private',
            $response->headers->get('Cache-Control'),
        );
        self::assertSame(
            'noindex, nofollow, noarchive',
            $response->headers->get('X-Robots-Tag'),
        );
    }
}
