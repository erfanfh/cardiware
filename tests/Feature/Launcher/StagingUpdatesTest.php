<?php

namespace Tests\Feature\Launcher;

use App\Application\Launcher\Actions\GetLauncherModsAction;
use App\Application\Launcher\Actions\PrepareLauncherContentDownloadAction;
use App\Application\Launcher\Actions\SyncLauncherSaleItemContentAction;
use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Application\Shared\Cache\CacheKey;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherDownloadSession;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use App\Http\Controllers\Api\V1\Launcher\LauncherContentController;
use App\Http\Controllers\Front\Commerce\DownloadLauncherContentFileController;
use App\Http\Controllers\Front\Commerce\StreamLauncherDownloadController;
use App\Http\Requests\Admin\Catalog\UpdateProductRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class StagingUpdatesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Test the real queries without the unrelated MySQL-only migration chain.
        config(['database.default' => 'staging_test', 'database.connections.staging_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ], 'launcher.allowed_download_hosts' => ['downloads.example.test']]);
        Cache::flush();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->json('metadata')->nullable();
        });
        Schema::create('product_sale_items', function (Blueprint $table): void {
            $table->id();
            $table->string('version')->default('1.0.0');
            $table->integer('manifest_revision')->default(1);
            $table->boolean('launcher_enabled')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('launcher_updates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_sale_item_id');
            $table->string('version');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('changelog')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('has_option_file')->default(false);
            $table->string('archive_format')->default('7z');
            $table->text('archive_password')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        $this->createModelTable('launcher_update_files', new LauncherUpdateFile);
        $this->createModelTable('launcher_download_sessions', new LauncherDownloadSession);
        $this->createModelTable('launcher_mods', new LauncherMod);
        Schema::create('launcher_mod_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('launcher_mod_id');
            $table->integer('sort_order')->default(0);
        });
        Schema::create('purchase_accesses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_sale_item_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
        });
        Schema::create('purchase_access_downloads', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_sale_item_id');
            $table->timestamp('invalidated_at')->nullable();
            $table->timestamp('session_expires_at')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_15_010000_add_staging_mode_columns.php'))->up();

        DB::table('users')->insert([['id' => 10], ['id' => 20], ['id' => 30]]);
        DB::table('product_sale_items')->insert([
            ['id' => 1, 'staging_user_ids' => '[10]'],
            ['id' => 2, 'staging_user_ids' => '[20]'],
        ]);
        foreach ([10, 20, 30] as $userId) {
            DB::table('purchase_accesses')->insert(['id' => $userId, 'user_id' => $userId, 'product_sale_item_id' => 1]);
        }
        $this->update(1, '1.1.0', false);
        $this->update(2, '1.2.0', true);
    }

    protected function tearDown(): void
    {
        DB::purge('staging_test');
        parent::tearDown();
    }

    private function createModelTable(string $name, Model $model): void
    {
        Schema::create($name, function (Blueprint $table) use ($model): void {
            $table->id();
            foreach ($model->getFillable() as $column) {
                $table->text($column)->nullable();
            }
            $table->timestamps();
        });
    }

    private function update(int $id, string $version, bool $staging, array $overrides = []): void
    {
        DB::table('launcher_updates')->insert([
            'id' => $id, 'product_sale_item_id' => 1, 'version' => $version, 'title' => $version,
            'is_staging' => $staging, 'published_at' => now()->subMinute(), ...$overrides,
        ]);
        DB::table('launcher_update_files')->insert([
            'id' => $id, 'launcher_update_id' => $id, 'file_name' => "update{$id}.7z",
            'url' => "https://downloads.example.test/update{$id}.7z", 'size_bytes' => 1024,
            'sha256' => str_repeat('a', 64), 'is_required' => true, 'sort_order' => 0,
        ]);
    }

    private function requestFor(int $userId): Request
    {
        $request = Request::create('/');
        $request->setUserResolver(fn () => (new User)->forceFill(['id' => $userId]));

        return $request;
    }

    private function assertNotFound(callable $action): void
    {
        try {
            $action();
        } catch (ModelNotFoundException|NotFoundHttpException) {
            $this->addToAssertionCount(1);

            return;
        }
        $this->fail('A hidden staged update must not be accessible.');
    }

    public function test_update_lists_keep_testers_and_public_separate_even_when_cached(): void
    {
        $controller = app(LauncherContentController::class);
        $this->update(3, '1.3.0', true, ['is_enabled' => false]);
        $this->update(4, '1.4.0', true, ['published_at' => now()->addDay()]);

        // Testers populate the cache first; ordinary users must still see only stable.
        $this->assertSame([1, 2], array_column($controller->updates($this->requestFor(10), 10)->getData(true)['data'], 'id'));
        $this->assertSame([1], array_column($controller->updates($this->requestFor(20), 20)->getData(true)['data'], 'id'));
        $this->assertSame([1], array_column($controller->updates($this->requestFor(30), 30)->getData(true)['data'], 'id'));

        // Removing a tester must take effect even if the tester response is still cached.
        DB::table('product_sale_items')->where('id', 1)->update(['staging_user_ids' => '[]']);
        $this->assertSame([1], array_column($controller->updates($this->requestFor(10), 10)->getData(true)['data'], 'id'));
    }

    public function test_mod_version_and_compatibility_use_only_the_callers_visible_updates(): void
    {
        DB::table('launcher_mods')->insert([
            'id' => 1, 'product_sale_item_id' => 1, 'name' => 'Test mod', 'slug' => 'test-mod',
            'version' => '1.0.0', 'is_enabled' => 1, 'published_at' => now()->subMinute(),
            'compatible_base_versions' => '["1.0.0"]', 'compatible_update_versions' => '["1.1.0"]',
            'size_bytes' => 0, 'priority' => 0,
        ]);
        $controller = app(LauncherContentController::class);
        $action = app(GetLauncherModsAction::class);
        $tester = $controller->mods($this->requestFor(10), 10, $action)->getData(true)['data'][0]['compatibility'];
        $public = $controller->mods($this->requestFor(20), 20, $action)->getData(true)['data'][0]['compatibility'];
        $this->assertSame('1.2.0', $tester['update_version']);
        $this->assertFalse($tester['is_compatible']);
        $this->assertSame('1.1.0', $public['update_version']);
        $this->assertTrue($public['is_compatible']);

        // Compatibility lists must not reveal even the staged version number.
        $state = new \ReflectionMethod($action, 'updateState');
        $this->assertSame(['1.1.0'], $state->invoke($action, ProductSaleItem::findOrFail(1), 20)['versions']);

        // A concurrent tester-list edit must not put tester content in a public cache entry.
        $access = PurchaseAccess::findOrFail(30);
        $access->setRelation('productSaleItem', ProductSaleItem::findOrFail(1));
        DB::table('product_sale_items')->where('id', 1)->update(['staging_user_ids' => '[10,30]']);
        $this->assertSame('1.1.0', $action->execute($access)[0]['compatibility']['update_version']);
    }

    public function test_saved_tester_and_staging_changes_invalidate_cached_lists(): void
    {
        $controller = app(LauncherContentController::class);
        $this->assertSame([1], array_column($controller->updates($this->requestFor(20), 20)->getData(true)['data'], 'id'));

        ProductSaleItem::findOrFail(1)->update(['staging_user_ids' => [10, 20]]);
        $this->assertSame(2, ProductSaleItem::findOrFail(1)->manifest_revision);
        $this->assertSame([1, 2], array_column($controller->updates($this->requestFor(20), 20)->getData(true)['data'], 'id'));

        LauncherUpdate::findOrFail(2)->update(['is_staging' => false]);
        $this->assertSame([1, 2], array_column($controller->updates($this->requestFor(30), 30)->getData(true)['data'], 'id'));
        LauncherUpdate::findOrFail(2)->update(['is_staging' => true]);
        $this->assertSame([1], array_column($controller->updates($this->requestFor(30), 30)->getData(true)['data'], 'id'));
    }

    public function test_migration_is_repeatable_and_defaults_existing_records_to_public(): void
    {
        $migration = require database_path('migrations/2026_10_15_010000_add_staging_mode_columns.php');
        $migration->up();
        DB::table('launcher_updates')->insert(['product_sale_item_id' => 1, 'title' => 'Legacy', 'version' => '0.9.0']);
        $this->assertFalse(LauncherUpdate::query()->latest('id')->firstOrFail()->is_staging);
        $migration->down();
        $this->assertFalse(Schema::hasColumn('launcher_updates', 'is_staging'));
        $this->assertFalse(Schema::hasColumn('product_sale_items', 'staging_user_ids'));
    }

    public static function preparationModes(): array
    {
        return [[false], [true]];
    }

    #[DataProvider('preparationModes')]
    public function test_download_preparation_accepts_only_product_testers_in_both_url_modes(bool $direct): void
    {
        config(['launcher.direct_download_enabled' => $direct, 'launcher.secure_link_secret' => '']);
        $resolver = $this->mock(RemoteDownloadFileMetadataResolver::class);
        $resolver->shouldReceive('resolve')->once()->andReturn([
            'size_bytes' => 1024, 'sha256' => str_repeat('a', 64), 'mime_type' => 'application/octet-stream',
        ]);
        $action = app(PrepareLauncherContentDownloadAction::class);
        $device = (new LauncherDevice)->forceFill(['id' => 1]);
        foreach ([20, 30] as $userId) {
            $this->assertNotFound(fn () => $action->execute(
                (new User)->forceFill(['id' => $userId]), $device, $userId, 'update', 2, 2, Str::uuid()->toString(),
            ));
        }
        $result = $action->execute(
            (new User)->forceFill(['id' => 10]), $device, 10, 'update', 2, 2, Str::uuid()->toString(),
        );
        $this->assertSame('update2.7z', $result['file_name']);
        $this->assertSame(1, LauncherDownloadSession::query()->count());
        if ($direct) {
            $this->assertSame('https://downloads.example.test/update2.7z', $result['url']);
        }
    }

    public function test_both_download_link_handlers_recheck_staging_permission(): void
    {
        $legacy = app(DownloadLauncherContentFileController::class);
        $stream = app(StreamLauncherDownloadController::class);
        $resolve = new \ReflectionMethod($stream, 'resolveDownload');
        $sessionId = Str::uuid()->toString();
        foreach ([10, 20] as $userId) {
            DB::table('launcher_download_sessions')->insert([
                'purchase_access_id' => $userId, 'content_type' => 'update', 'content_id' => 2,
                'file_id' => 2, 'download_session_id' => $sessionId, 'session_expires_at' => now()->addHour(),
            ]);
        }
        $tester = PurchaseAccess::findOrFail(10);
        $ordinary = PurchaseAccess::findOrFail(20);
        $this->assertSame('https://downloads.example.test/update2.7z', $legacy($tester, 'update', 2, 2)->getTargetUrl());
        $this->assertSame('update', $resolve->invoke($stream, $tester, 'update', 2, 2, $sessionId)[0]);
        $this->assertNotFound(fn () => $legacy($ordinary, 'update', 2, 2));
        $this->assertNotFound(fn () => $resolve->invoke($stream, $ordinary, 'update', 2, 2, $sessionId));

        DB::table('product_sale_items')->where('id', 1)->update(['staging_user_ids' => '[]']);
        $this->assertNotFound(fn () => $legacy($tester, 'update', 2, 2));
        $this->assertNotFound(fn () => $resolve->invoke($stream, $tester, 'update', 2, 2, $sessionId));
    }

    public function test_switching_staging_off_publishes_to_ordinary_users(): void
    {
        Model::withoutEvents(fn () => app(SyncLauncherSaleItemContentAction::class)->execute(ProductSaleItem::findOrFail(1), [
            'launcher_updates' => [[
                'id' => 2, 'title' => 'Go live', 'version' => '1.2.0', 'is_enabled' => true, 'is_staging' => '0',
                'files' => [[
                    'id' => 2, 'file_name' => 'update2.7z', 'url' => 'https://downloads.example.test/update2.7z',
                    'size_bytes' => 1024, 'sha256' => str_repeat('a', 64),
                ]],
            ]],
        ]));
        $this->assertFalse(LauncherUpdate::findOrFail(2)->is_staging);
        $visible = LauncherUpdate::query()->published()->visibleToLauncherUser(30, [10])->pluck('id')->all();
        $this->assertSame([2], $visible);
    }

    public function test_request_preserves_tester_ids_and_validates_real_accounts(): void
    {
        $request = new UpdateProductRequest(['sale_items' => [[
            'staging_user_ids' => "10, 20\n30،10", 'launcher_updates' => [['is_staging' => '1']],
        ]]]);
        $normalize = new \ReflectionMethod($request, 'normalizeSaleItemsForValidation');
        $items = $normalize->invoke($request);
        $this->assertSame(['10', '20', '30', '10'], $items[0]['staging_user_ids']);
        $this->assertTrue($items[0]['launcher_updates'][0]['is_staging']);
        $rules = $request->rules();
        $rules = array_intersect_key($rules, array_flip(['sale_items.*.staging_user_ids', 'sale_items.*.staging_user_ids.*']));
        $this->assertTrue(Validator::make(['sale_items' => $items], $rules)->passes());
        foreach ([['unknown'], [999], [0], [-1]] as $invalid) {
            $this->assertTrue(Validator::make(['sale_items' => [['staging_user_ids' => $invalid]]], $rules)->fails());
        }
    }

    public function test_scope_is_product_specific_and_denies_anonymous_users(): void
    {
        $this->assertSame([1], LauncherUpdate::query()->visibleToLauncherUser(null, [10])->pluck('id')->all());
        $this->assertFalse(ProductSaleItem::findOrFail(1)->isLauncherStagingTester(20));
        $this->assertTrue(ProductSaleItem::findOrFail(2)->isLauncherStagingTester(20));
        $this->assertNotSame(CacheKey::launcherUpdates(1, 1, true), CacheKey::launcherUpdates(1, 1, false));
        $this->assertNotSame(CacheKey::launcherMods(1, 1, true), CacheKey::launcherMods(1, 1, false));
    }

    public function test_admin_controls_exist_for_simple_and_variable_products(): void
    {
        foreach (['simple', 'variable'] as $mode) {
            $html = Blade::render('<x-admin.catalog.launcher-content-fields :mode="$mode" />', ['mode' => $mode]);
            $this->assertStringContainsString('[staging_user_ids]', $html);
            $this->assertStringContainsString('x-model="update.is_staging"', $html);
            $this->assertStringContainsString('شناسه کلید مانیفست را تغییر ندهید', $html);
        }
    }
}
