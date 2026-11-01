<?php

namespace Tests\Feature\Content;

use App\Application\Content\Support\ProductLandingContentSchema;
use App\Application\Shared\Cache\Invalidators\SettingCacheInvalidator;
use App\Application\System\Actions\UpdateSettingAction;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\User;
use App\Domain\System\Models\Setting;
use App\Http\Middleware\HandleSeoRedirectsMiddleware;
use App\Http\Middleware\RecordDailyLoyaltyVisit;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\ProductLandingsSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductLandingsSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        $this->withoutVite();
        $this->withoutMiddleware([HandleSeoRedirectsMiddleware::class, RecordDailyLoyaltyVisit::class]);
    }

    private function admin(): User
    {
        $role = Role::factory()->create(['is_super_admin' => true]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function payload(string $key, array $content): array
    {
        $settings = app(ProductLandingsSettings::class);

        return ['payload' => json_encode($content), 'version' => $settings->version($settings->document($key))];
    }

    public function test_all_shipped_defaults_are_valid_and_are_used_without_database_rows(): void
    {
        $schema = app(ProductLandingContentSchema::class);
        $settings = app(ProductLandingsSettings::class);

        foreach (ProductLandingsSettings::defaults() as $key => $default) {
            $schema->validate($default, $schema->infer([$default]));
            self::assertSame($default, $settings->document($key));
        }
        self::assertSame(0, Setting::query()->where('group', 'product_landings')->count());
    }

    public function test_admin_can_open_all_editors_and_save_original_values_without_changes(): void
    {
        $this->actingAs($this->admin());

        foreach (ProductLandingsSettings::defaults() as $key => $default) {
            $this->get(route('admin.settings.product-landings', $key))
                ->assertOk()->assertSee('name="payload"', false)->assertSee('name="version"', false)
                ->assertDontSee('@js(', false);
            $this->put(route('admin.settings.product-landings.store', $key), $this->payload($key, $default))
                ->assertSessionHasNoErrors()->assertRedirect(route('admin.settings.product-landings', $key));
            self::assertSame($default, app(ProductLandingsSettings::class)->document($key));
        }
    }

    public function test_updates_are_visible_in_both_languages_and_do_not_change_the_other_product(): void
    {
        $this->actingAs($this->admin());
        $settings = app(ProductLandingsSettings::class);
        $originalOther = $settings->document('fifa23');
        $content = $settings->document('ultimate');
        $content['sections'][0]['title'] = ['fa' => 'عنوان تازه لندینگ', 'en' => 'A new landing title'];
        $content['buy_url'] = '/products/new-product';
        $content['sections'][1]['items'] = [];

        $this->put(route('admin.settings.product-landings.store', 'ultimate'), $this->payload('ultimate', $content))
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->get('/pgl-ultimate')->assertOk()->assertSee('عنوان تازه لندینگ')->assertSee('/products/new-product');
        $this->get('/en/pgl-ultimate')->assertOk()->assertSee('A new landing title');
        self::assertSame([], $settings->document('ultimate')['sections'][1]['items']);
        self::assertSame($originalOther, $settings->document('fifa23'));
        BaseSettings::clearResolvedGroups();
        self::assertSame($content, app(ProductLandingsSettings::class)->document('ultimate'));
    }

    public function test_all_tabs_save_when_an_earlier_json_operation_failed_in_the_same_request(): void
    {
        $this->actingAs($this->admin());
        $this->app->resolving(UpdateSettingAction::class, static function (): void {
            json_decode('{invalid json');
        });

        foreach (ProductLandingsSettings::defaults() as $key => $content) {
            $this->put(route('admin.settings.product-landings.store', $key), $this->payload($key, $content))
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.settings.product-landings', $key));

            self::assertSame($content, app(ProductLandingsSettings::class)->document($key));
        }
    }

    public function test_shared_page_and_catalog_updates_reach_details_and_home(): void
    {
        $this->actingAs($this->admin());
        $settings = app(ProductLandingsSettings::class);
        $pages = $settings->document('pages');
        $pages['leagues']['title']['fa'] = 'لیگ‌های ویرایش‌شده';
        $this->put(route('admin.settings.product-landings.store', 'pages'), $this->payload('pages', $pages))->assertSessionHasNoErrors();

        $catalog = $settings->document('catalog');
        $catalog['leagues']['persian-gulf-pro-league']['name']['fa'] = 'نام تازه لیگ';
        $catalog['leagues']['persian-gulf-pro-league']['logo'] = 'https://example.com/new-league-logo.png';
        $this->put(route('admin.settings.product-landings.store', 'catalog'), $this->payload('catalog', $catalog))->assertSessionHasNoErrors();

        foreach (['pgl-ultimate', 'pgl-fifa-23'] as $slug) {
            $this->get('/'.$slug.'/leagues')->assertOk()->assertSee('لیگ‌های ویرایش‌شده')->assertSee('نام تازه لیگ');
            $this->get('/'.$slug.'/leagues/persian-gulf-pro-league')->assertOk()->assertSee('نام تازه لیگ')
                ->assertSee('https://example.com/new-league-logo.png', false);
            $this->get('/'.$slug)->assertOk()->assertSee('نام تازه لیگ');
        }
    }

    public function test_stale_form_cannot_overwrite_a_newer_save(): void
    {
        $this->actingAs($this->admin());
        $settings = app(ProductLandingsSettings::class);
        $content = $settings->document('ultimate');
        $stale = $this->payload('ultimate', $content);
        $content['brand'] = 'NEW BRAND';
        $settings->set('ultimate', $content);

        $this->put(route('admin.settings.product-landings.store', 'ultimate'), $stale)->assertSessionHasErrors('version');
        self::assertSame('NEW BRAND', $settings->document('ultimate')['brand']);
    }

    public function test_legacy_documents_can_omit_the_new_visibility_and_squad_fields(): void
    {
        $this->actingAs($this->admin());
        $removeNewFields = function (array $content) use (&$removeNewFields): array {
            foreach ($content as $key => $value) {
                if (in_array($key, ['enabled', 'hidden', 'pgl_team_id'], true)) {
                    unset($content[$key]);
                } elseif (is_array($value)) {
                    $content[$key] = $removeNewFields($value);
                }
            }

            return $content;
        };
        $content = $removeNewFields(ProductLandingsSettings::defaults()['ultimate']);

        $this->put(route('admin.settings.product-landings.store', 'ultimate'), $this->payload('ultimate', $content))
            ->assertSessionHasNoErrors()->assertRedirect();
        self::assertSame(true, app(ProductLandingsSettings::class)->document('ultimate')['enabled']);
    }

    public function test_invalid_content_is_rejected_without_persisting_any_changes(): void
    {
        $this->actingAs($this->admin());
        $original = app(ProductLandingsSettings::class)->document('ultimate');
        foreach (['link', 'type', 'missing', 'extra', 'shape', 'theme'] as $case) {
            $content = $original;
            match ($case) {
                'link' => $content['buy_url'] = 'javascript:alert(1)',
                'type' => $content['sections'][0]['type'] = '../master',
                'missing' => $content = array_diff_key($content, ['sections' => true]),
                'extra' => $content['unexpected'] = 'value',
                'shape' => $content['sections'] = 'invalid',
                'theme' => $content['theme'] = 'other-theme',
            };
            $this->put(route('admin.settings.product-landings.store', 'ultimate'), $this->payload('ultimate', $content))->assertSessionHasErrors();
            self::assertSame($original, app(ProductLandingsSettings::class)->document('ultimate'));
        }
        self::assertSame(0, Setting::query()->where('group', 'product_landings')->count());
    }

    public function test_admin_without_setting_permission_cannot_read_or_write(): void
    {
        $role = Role::factory()->create();
        $role->permissions()->attach(Permission::query()->create(['group' => 'admin', 'ability' => 'display', 'label' => 'پنل'])->id);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        $this->get(route('admin.settings.product-landings'))->assertForbidden();
        $this->put(route('admin.settings.product-landings.store', 'ultimate'), [])->assertForbidden();
    }

    public function test_delegated_admin_with_the_specific_permission_can_edit(): void
    {
        $role = Role::factory()->create();
        foreach (['admin' => 'display', 'setting' => 'product-landings'] as $group => $ability) {
            $role->permissions()->attach(Permission::query()->firstOrCreate(
                compact('group', 'ability'), ['label' => 'مجوز تنظیمات لندینگ'],
            )->id);
        }
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));

        $this->get(route('admin.settings.product-landings'))->assertOk();
        $default = ProductLandingsSettings::defaults()['ultimate'];
        $this->put(route('admin.settings.product-landings.store', 'ultimate'), $this->payload('ultimate', $default))
            ->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_malformed_json_is_rejected_without_writing_settings(): void
    {
        $this->actingAs($this->admin());
        $payload = $this->payload('ultimate', ProductLandingsSettings::defaults()['ultimate']);
        $payload['payload'] = '{invalid json';

        $this->put(route('admin.settings.product-landings.store', 'ultimate'), $payload)->assertSessionHasErrors('payload');
        self::assertSame(0, Setting::query()->where('group', 'product_landings')->count());
    }

    public function test_persistence_failure_keeps_original_exception_and_rolls_back(): void
    {
        $this->actingAs($this->admin());
        $this->withoutExceptionHandling();
        $content = ProductLandingsSettings::defaults()['ultimate'];
        $payload = $this->payload('ultimate', $content);
        $failure = new \RuntimeException('Original failure during settings invalidation');
        $this->mock(SettingCacheInvalidator::class)
            ->shouldReceive('afterUpdated')->with('productLandings', 'ultimate')->once()->andThrow($failure);

        try {
            $this->put(route('admin.settings.product-landings.store', 'ultimate'), $payload);
            self::fail('The original exception must be reported by Laravel.');
        } catch (\RuntimeException $exception) {
            self::assertSame($failure, $exception);
        }

        self::assertSame(0, Setting::query()->where('group', 'product_landings')->count());
        self::assertSame($content, app(ProductLandingsSettings::class)->document('ultimate'));
    }
}
