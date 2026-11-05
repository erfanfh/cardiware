<?php

namespace Tests\Feature\Content;

use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Http\Middleware\ForceTrailingSlash;
use App\Http\Middleware\HandleSeoRedirectsMiddleware;
use App\Http\Middleware\RedirectToCanonicalUrl;
use App\Http\Middleware\RecordDailyLoyaltyVisit;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\ProductLandingsSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CustomProductLandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Cache::flush();
        $this->withoutVite();
        $this->withoutMiddleware([HandleSeoRedirectsMiddleware::class, RecordDailyLoyaltyVisit::class, ForceTrailingSlash::class, RedirectToCanonicalUrl::class]);
    }

    protected function tearDown(): void
    {
        BaseSettings::clearResolvedGroups();
        parent::tearDown();
    }

    private function admin(): User
    {
        $role = Role::factory()->create(['is_super_admin' => true]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_admin_adds_a_new_product_landing_that_stays_hidden_until_enabled(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.settings.product-landings.custom.store'), ['label' => 'PGL 6', 'slug' => 'pgl-6', 'base' => 'ultimate'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.settings.product-landings', 'p_6'));

        $this->get(route('admin.settings.product-landings', 'p_6'))->assertOk()->assertSee('PGL 6')->assertSee('/pgl-6');
        $this->get(url('/pgl-6') . '/')->assertNotFound();

        $settings = app(ProductLandingsSettings::class);
        $content = $settings->document('p_6');
        self::assertFalse($content['enabled']);
        $content['enabled'] = true;
        $content['seo']['title'] = ['fa' => 'لندینگ PGL 6', 'en' => 'PGL 6 landing'];

        $this->put(route('admin.settings.product-landings.store', 'p_6'), [
            'payload' => json_encode($content),
            'version' => $settings->version($settings->document('p_6')),
        ])->assertSessionHasNoErrors()->assertRedirect();

        BaseSettings::clearResolvedGroups();
        $this->get(url('/pgl-6') . '/')->assertOk()->assertSee('PGL 6');
        $this->get(url('/pgl-6/leagues/persian-gulf-pro-league'))->assertOk();
        // لندینگ پایه دست نخورده است.
        self::assertSame(ProductLandingsSettings::defaults()['ultimate'], app(ProductLandingsSettings::class)->document('ultimate'));
    }

    public function test_invalid_or_duplicate_slugs_are_refused_and_unknown_pgl_pages_return_404(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.settings.product-landings.custom.store'), ['label' => 'X', 'slug' => 'about-us', 'base' => 'ultimate'])
            ->assertSessionHasErrors('slug');
        $this->post(route('admin.settings.product-landings.custom.store'), ['label' => 'Ultimate', 'slug' => 'pgl-ultimate', 'base' => 'ultimate'])
            ->assertSessionHasErrors('slug');

        $this->get(url('/pgl-not-created') . '/')->assertNotFound();
        $this->get(url('/pgl-not-created/leagues/persian-gulf-pro-league'))->assertNotFound();
        $this->get(route('admin.settings.product-landings', 'p_missing'))->assertNotFound();
    }

    public function test_custom_landing_can_be_deleted(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('admin.settings.product-landings.custom.store'), ['label' => 'PGL 7', 'slug' => 'pgl-7', 'base' => 'fifa23']);

        $this->delete(route('admin.settings.product-landings.custom.destroy', 'p_7'))->assertRedirect();

        BaseSettings::clearResolvedGroups();
        self::assertSame([], app(ProductLandingsSettings::class)->customProducts());
        $this->get(route('admin.settings.product-landings', 'p_7'))->assertNotFound();
    }
}
