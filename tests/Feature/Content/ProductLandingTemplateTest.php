<?php

namespace Tests\Feature\Content;

use App\Application\Content\Actions\GetPageDetailDataAction;
use App\Application\Content\DTOs\ResolvedContentDTO;
use App\Application\Content\Services\PageTemplateService;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Content\Models\Page;
use App\Http\Middleware\ForceTrailingSlash;
use App\Http\Middleware\HandleSeoRedirectsMiddleware;
use App\Http\Middleware\RecordDailyLoyaltyVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductLandingTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
