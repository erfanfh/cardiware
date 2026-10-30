<?php

namespace Tests\Feature\Content;

use App\Application\Content\Actions\GetPageDetailDataAction;
use App\Application\Content\DTOs\ResolvedContentDTO;
use App\Application\Content\Services\PageTemplateService;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Content\Models\Page;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Http\Middleware\ForceTrailingSlash;
use App\Http\Middleware\HandleSeoRedirectsMiddleware;
use App\Http\Middleware\RecordDailyLoyaltyVisit;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductLandingTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();

        $this->withoutVite();
        $this->withoutMiddleware([
            HandleSeoRedirectsMiddleware::class,
            RecordDailyLoyaltyVisit::class,
        ]);
    }

    public static function landingUrls(): iterable
    {
        foreach (['pgl-ultimate', 'pgl-fifa-23'] as $product) {
            foreach (['' => ['fa', 'rtl'], '/en' => ['en', 'ltr']] as $prefix => [$locale, $direction]) {
                foreach (['', '/leagues', '/stadiums', '/trophies', '/faces', '/leagues/persian-gulf-pro-league', '/leagues/persian-gulf-pro-league/sepahan-fc'] as $path) {
                    yield $prefix.'/'.$product.$path => [$prefix.'/'.$product.$path, $locale, $direction];
                }
            }
        }
    }

    #[DataProvider('landingUrls')]
    public function test_original_landing_pages_render(string $url, string $locale, string $direction): void
    {
        if (in_array($url, ['/pgl-ultimate', '/pgl-fifa-23'], true)) {
            $role = Role::factory()->create(['is_super_admin' => true]);
            $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        }

        $this->get($url)
            ->assertOk()
            ->assertSee('<html lang="'.$locale.'" dir="'.$direction.'">', false)
            ->assertSee('class="landing landing--', false);
    }

    public function test_landing_routes_do_not_capture_existing_site_routes(): void
    {
        foreach (['/about-us' => 'contents.detail', '/posts' => 'posts.list', '/' => 'home', '/launcher' => 'launcher.landing'] as $url => $name) {
            self::assertSame($name, Route::getRoutes()->match(Request::create($url))->getName());
        }
    }

    public function test_both_templates_are_available_in_the_page_editor(): void
    {
        $templates = app(PageTemplateService::class)->all();

        self::assertSame('لندینگ PGL Ultimate 27', $templates['pgl-ultimate']);
        self::assertSame('لندینگ PGL FIFA 23', $templates['pgl-fifa-23']);
    }

    public static function restrictedLandingUrls(): iterable
    {
        yield ['/pgl-ultimate'];
        yield ['/pgl-fifa-23'];
    }

    #[DataProvider('restrictedLandingUrls')]
    public function test_guest_receives_404_on_restricted_landing(string $url): void
    {
        $this->get($url)->assertNotFound();
        $this->head($url)->assertNotFound();
        $this->get($url.'/?preview=1')->assertNotFound();
    }

    #[DataProvider('restrictedLandingUrls')]
    public function test_regular_user_receives_404_on_restricted_landing(string $url): void
    {
        $this->actingAs(User::factory()->create());

        $this->get($url)->assertNotFound();
    }

    #[DataProvider('restrictedLandingUrls')]
    public function test_admin_without_super_admin_role_receives_404(string $url): void
    {
        $role = Role::factory()->create(['is_super_admin' => false]);
        $role->permissions()->attach(Permission::query()->create([
            'group' => 'admin', 'ability' => 'display', 'label' => 'پنل مدیریت',
        ])->id);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));

        $this->get($url)->assertNotFound();
    }

    #[DataProvider('restrictedLandingUrls')]
    public function test_super_admin_can_view_restricted_landing_without_public_caching(string $url): void
    {
        $role = Role::factory()->create(['is_super_admin' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));

        $response = $this->get($url)->assertOk()->assertViewIs('front.landings.page');
        self::assertTrue($response->headers->hasCacheControlDirective('private'));
        self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
    }

    public static function templateNames(): iterable
    {
        yield ['pgl-ultimate'];
        yield ['pgl-fifa-23'];
    }

    #[DataProvider('templateNames')]
    public function test_cms_page_renders_the_selected_landing_template(string $template): void
    {
        $this->withoutMiddleware(ForceTrailingSlash::class);
        $page = new Page(['template' => $template, 'title' => 'Landing']);

        Route::bind('slug', static fn () => ResolvedContentDTO::page($page));
        $this->mock(GetPageDetailDataAction::class)
            ->shouldReceive('execute')->with($page)->once()->andReturn($page);
        $this->mock(StoreViewAction::class)
            ->shouldReceive('execute')->with($page)->once();

        $this->get('/campaign')
            ->assertOk()
            ->assertViewIs('templates.'.$template)
            ->assertSee('landing--'.$template, false)
            ->assertSee('<html lang="fa" dir="rtl">', false);
    }

    public function test_unknown_landing_detail_is_not_found(): void
    {
        $this->get('/pgl-ultimate/missing')->assertNotFound();
        $this->get('/pgl-ultimate/leagues/missing')->assertNotFound();
        $this->get('/pgl-ultimate/leagues/persian-gulf-pro-league/missing')->assertNotFound();
    }
}
