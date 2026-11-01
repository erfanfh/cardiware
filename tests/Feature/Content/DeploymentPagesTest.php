<?php

namespace Tests\Feature\Content;

use App\Http\Middleware\ForceTrailingSlash;
use App\Http\Middleware\HandleSeoRedirectsMiddleware;
use App\Http\Middleware\RedirectToCanonicalUrl;
use App\Infrastructure\Services\Setting\BaseSettings;
use Database\Seeders\Layout\PageLayoutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_imported_public_pages_render_with_the_new_home_layout(): void
    {
        BaseSettings::clearResolvedGroups();
        $this->withoutVite();
        $this->withoutMiddleware([
            ForceTrailingSlash::class,
            HandleSeoRedirectsMiddleware::class,
            RedirectToCanonicalUrl::class,
        ]);
        $this->seed(PageLayoutSeeder::class);

        $bufferLevel = ob_get_level();
        try {
            foreach (['/', '/launcher', '/auth', '/gallery'] as $url) {
                $this->get($url)->assertOk();
            }
        } finally {
            // Livewire's page rendering opens a buffer that persists across requests.
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }
    }
}
