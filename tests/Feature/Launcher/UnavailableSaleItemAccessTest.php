<?php

namespace Tests\Feature\Launcher;

use App\Application\Commerce\Actions\Access\EnsurePurchaseAccessCanDownloadFileAction;
use App\Application\Commerce\Actions\Access\GetUserLibraryAction;
use App\Application\Commerce\Actions\Access\GetUserLibraryItemAction;
use App\Application\Commerce\Actions\Access\GetUserLibraryManifestAction;
use App\Application\Commerce\Actions\Access\GetUserPurchaseAccessesAction;
use App\Application\Commerce\Actions\Access\PreparePurchaseAccessFileDownloadAction;
use App\Application\Commerce\Actions\Access\RecordPurchaseAccessDownloadAction;
use App\Application\Commerce\Actions\Cart\ValidatePurchasableForCartAction;
use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Application\Launcher\Actions\ClaimFreeSaleItemAction;
use App\Application\Launcher\Actions\GetLauncherCatalogAction;
use App\Application\Launcher\Actions\GetLauncherModsAction;
use App\Application\Launcher\Actions\PrepareLauncherContentDownloadAction;
use App\Application\Launcher\Actions\Security\IssueOfflineLicenseAction;
use App\Application\Launcher\Exceptions\LauncherCommerceException;
use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductInstallationStep;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Catalog\Models\ProductSaleItemManifest;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Models\PurchaseAccessDownload;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherDownloadSession;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use App\Http\Controllers\Api\V1\Launcher\LauncherCommerceController;
use App\Http\Controllers\Api\V1\Launcher\LauncherContentController;
use App\Http\Controllers\Front\Commerce\DownloadPurchasedFileController;
use App\Http\Resources\Api\V1\Launcher\LauncherCatalogItemResource;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Livewire\Front\Account\MyDownloadsSection;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UnavailableSaleItemAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Exercise the real queries on an isolated DB; historical migrations require MySQL.
        config(['database.default' => 'unavailable_test', 'database.connections.unavailable_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ], 'launcher.allowed_download_hosts' => ['downloads.example.test']]);
        Cache::flush();
        Paginator::currentPageResolver(fn () => 1);

        foreach ([
            new User, new Product, new ProductSaleItem, new ProductSaleItemFile,
            new ProductSaleItemDiscount, new ProductSaleItemManifest, new ProductInstallationStep,
            new PurchaseAccess, new PurchaseAccessDownload, new Order, new LauncherDevice,
            new LauncherUpdate, new LauncherUpdateFile, new LauncherMod, new LauncherModFile,
            new LauncherDownloadSession,
        ] as $model) {
            Schema::create($model->getTable(), function (Blueprint $table) use ($model): void {
                $table->id();
                foreach ($model->getFillable() as $column) {
                    $table->text($column)->nullable();
                }
                if (in_array($model::class, [User::class, Product::class, ProductSaleItem::class], true)) {
                    $table->softDeletes();
                }
                $table->timestamps();
            });
        }
        DB::table('users')->insert(['id' => 1, 'active_device_slot' => 1]);
        DB::table('users')->insert(['id' => 2, 'active_device_slot' => 1]);
        DB::table('products')->insert([
            'id' => 1, 'title' => 'Game', 'slug' => 'game', 'status' => 'active',
            'published_at' => now()->subDay(),
        ]);
        DB::table('product_sale_items')->insert([
            'id' => 1, 'product_id' => 1, 'title' => 'Retired edition', 'sku' => 'RETIRED',
            'status' => 'available', 'base_price' => 1000, 'launcher_enabled' => true,
            'launcher_free_claim_enabled' => false, 'version' => '1.0.0', 'manifest_revision' => 1,
        ]);
        DB::table('product_sale_item_files')->insert([
            'id' => 1, 'product_sale_item_id' => 1, 'title' => 'Base', 'file_name' => 'base.7z',
            'url' => 'https://downloads.example.test/base.7z', 'size_bytes' => 1024,
            'sha256' => str_repeat('a', 64), 'is_active' => true, 'is_required' => true, 'sort_order' => 0,
        ]);
        DB::table('purchase_accesses')->insert([
            'id' => 1, 'user_id' => 1, 'product_id' => 1, 'product_sale_item_id' => 1, 'status' => 'active',
        ]);
        DB::table('product_sale_item_manifests')->insert([
            'product_sale_item_id' => 1, 'schema_version' => 1, 'download_type' => 'single_file',
            'assembly_strategy' => 'independent', 'validation_mode' => 'per_file',
            'install_folder_name' => 'Game', 'main_executable_path' => 'Game.exe',
        ]);
        DB::table('launcher_devices')->insert([
            'id' => 1, 'user_id' => 1, 'device_id' => Str::uuid()->toString(), 'device_slot' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('unavailable_test');
        parent::tearDown();
    }

    private function user(int $id = 1): User
    {
        return User::findOrFail($id);
    }

    private function request(int $userId = 1): Request
    {
        $request = Request::create('/', 'POST', ['return_uri' => 'mwlauncher://purchase-complete']);
        $request->setUserResolver(fn () => $this->user($userId));

        return $request;
    }

    private function retire(): void
    {
        ProductSaleItem::findOrFail(1)->update(['status' => 'unavailable']);
    }

    private function catalog(int $userId = 1, ?string $search = null, int $perPage = 20): mixed
    {
        return app(GetLauncherCatalogAction::class)->execute($this->user($userId), $search, $perPage);
    }

    public function test_availability_changes_refresh_warm_catalogs_without_revoking_ownership(): void
    {
        $this->assertSame([1], $this->catalog(2)->getCollection()->modelKeys());
        $this->assertTrue(LauncherCatalogItemResource::make(app(GetLauncherCatalogAction::class)
            ->find($this->user(2), 1))->resolve($this->request(2))['actions']['can_buy']);
        $this->retire();

        $this->assertSame([1], $this->catalog()->getCollection()->modelKeys());
        $this->assertSame([], $this->catalog(2)->getCollection()->modelKeys());
        $detail = LauncherCatalogItemResource::make(app(GetLauncherCatalogAction::class)
            ->find($this->user(), 1))->resolve($this->request());
        $this->assertTrue($detail['ownership']['has_access']);
        $this->assertSame(1, $detail['ownership']['access_id']);
        $this->assertSame(['can_buy' => false, 'can_claim' => false, 'can_install' => true], $detail['actions']);
        $this->assertTrue($detail['capabilities']['supports_launch']);

        ProductSaleItem::findOrFail(1)->update(['status' => 'available']);
        $this->assertSame([1], $this->catalog(2)->getCollection()->modelKeys());
    }

    public static function invalidAccesses(): array
    {
        return [
            'revoked' => [['status' => 'revoked']],
            'expired status' => [['status' => 'expired']],
            'expired date' => [['expires_at' => '2020-01-01 00:00:00']],
            'not started' => [['starts_at' => '2099-01-01 00:00:00']],
            'disabled historical free claim' => [['order_id' => 1]],
        ];
    }

    #[DataProvider('invalidAccesses')]
    public function test_warm_owner_cache_never_grants_retired_items_to_invalid_accesses(array $changes): void
    {
        $this->retire();
        $this->assertSame([1], $this->catalog()->getCollection()->modelKeys());
        DB::table('orders')->insert(['id' => 1, 'metadata' => '{"source":"launcher_free_claim"}']);
        // Bypass cache invalidation deliberately: authorization must be resolved live.
        DB::table('purchase_accesses')->where('id', 1)->update($changes);
        $this->assertSame([], $this->catalog()->getCollection()->modelKeys());
        $detail = LauncherCatalogItemResource::make(app(GetLauncherCatalogAction::class)
            ->find($this->user(), 1))->resolve($this->request());
        $this->assertFalse($detail['ownership']['has_access']);
        $this->assertSame(['can_buy' => false, 'can_claim' => false, 'can_install' => false], $detail['actions']);
    }

    public function test_catalog_search_pagination_and_other_editions_remain_correct(): void
    {
        $this->retire();
        DB::table('product_sale_items')->insert([
            'id' => 2, 'product_id' => 1, 'title' => 'Current edition', 'status' => 'available',
            'launcher_enabled' => true, 'launcher_sort_order' => 1,
        ]);
        DB::table('product_sale_items')->insert([
            'id' => 3, 'product_id' => 1, 'title' => 'Unowned retired edition', 'status' => 'unavailable',
            'launcher_enabled' => true, 'launcher_sort_order' => 2,
        ]);
        $page = $this->catalog(1, null, 1);
        $this->assertSame(2, $page->total());
        $this->assertSame([1], $page->getCollection()->modelKeys());
        Paginator::currentPageResolver(fn () => 2);
        $this->assertSame([2], $this->catalog(1, null, 1)->getCollection()->modelKeys());
        Paginator::currentPageResolver(fn () => 1);
        $this->assertSame([1], $this->catalog(1, 'RETIRED')->getCollection()->modelKeys());
        $this->assertSame([], $this->catalog(2, 'RETIRED')->getCollection()->modelKeys());
        $this->assertSame([2], $this->catalog(2)->getCollection()->modelKeys());
    }

    public function test_website_account_and_launcher_library_keep_files_and_manifest_available(): void
    {
        $this->retire();
        $access = app(GetUserPurchaseAccessesAction::class)->execute(1)->items()[0];
        $this->assertSame(1, $access->productSaleItem->files->first()->id);
        $this->assertTrue(app(MyDownloadsSection::class)->fileIsUsable($access, $access->productSaleItem->files->first()));
        $library = app(GetUserLibraryAction::class)->execute($this->user());
        $this->assertSame([1], $library->getCollection()->modelKeys());
        $item = app(GetUserLibraryItemAction::class)->execute($this->user(), 1);
        $this->assertTrue($item->productSaleItem->files->first()->can_download);
        $manifest = app(GetUserLibraryManifestAction::class)->execute($this->user(), 1);
        $this->assertTrue($manifest['access']['is_usable']);
        $this->assertSame(1, $manifest['download']['files'][0]['file_id']);
        $this->assertTrue($manifest['capabilities']['supports_install']);
        $this->assertTrue($manifest['capabilities']['supports_launch']);

        $response = app(DownloadPurchasedFileController::class)(
            $access, $access->productSaleItem->files->first(), $this->request(),
            app(EnsurePurchaseAccessCanDownloadFileAction::class), app(RecordPurchaseAccessDownloadAction::class),
        );
        $this->assertSame('https://downloads.example.test/base.7z', $response->getTargetUrl());
        $this->assertSame(1, PurchaseAccessDownload::count());
    }

    public function test_retiring_does_not_interrupt_a_launcher_download_or_change_its_manifest_revision(): void
    {
        $resolver = Mockery::mock(RemoteDownloadFileMetadataResolver::class);
        $resolver->shouldReceive('resolve')->times(3)->andReturn([
            'size_bytes' => 1024, 'sha256' => str_repeat('a', 64), 'mime_type' => 'application/octet-stream',
        ]);
        $this->app->instance(RemoteDownloadFileMetadataResolver::class, $resolver);
        $action = app(PreparePurchaseAccessFileDownloadAction::class);
        $session = Str::uuid()->toString();
        $before = $action->execute($this->user(), LauncherDevice::findOrFail(1), 1, 1, $session);
        $this->retire();
        $after = $action->execute($this->user(), LauncherDevice::findOrFail(1), 1, 1, $session);
        $this->assertFalse($before['reused_session']);
        $this->assertTrue($after['reused_session']);
        $this->assertSame(1, $after['manifest_revision']);
        $this->assertSame(1, PurchaseAccessDownload::count());
        $this->assertNull(PurchaseAccessDownload::first()->invalidated_at);
        $fresh = $action->execute($this->user(), LauncherDevice::findOrFail(1), 1, 1, Str::uuid()->toString());
        $this->assertFalse($fresh['reused_session']);
        $this->assertSame(2, PurchaseAccessDownload::count());
    }

    public function test_updates_and_mods_can_still_be_listed_and_downloaded_for_retired_items(): void
    {
        $this->retire();
        DB::table('launcher_updates')->insert([
            'id' => 1, 'product_sale_item_id' => 1, 'version' => '1.1.0', 'title' => 'Update',
            'is_enabled' => true, 'is_staging' => false, 'published_at' => now()->subDay(),
        ]);
        DB::table('launcher_update_files')->insert([
            'id' => 1, 'launcher_update_id' => 1, 'file_name' => 'update.7z',
            'url' => 'https://downloads.example.test/update.7z', 'size_bytes' => 1024,
            'sha256' => str_repeat('a', 64),
        ]);
        DB::table('launcher_mods')->insert([
            'id' => 1, 'product_sale_item_id' => 1, 'name' => 'Mod', 'slug' => 'mod',
            'is_enabled' => true, 'published_at' => now()->subDay(), 'size_bytes' => 1024,
            'compatible_base_versions' => '["1.0.0"]', 'compatible_update_versions' => '["1.1.0"]',
        ]);
        DB::table('launcher_mod_files')->insert([
            'id' => 1, 'launcher_mod_id' => 1, 'file_name' => 'mod.7z',
            'url' => 'https://downloads.example.test/mod.7z', 'size_bytes' => 1024,
            'sha256' => str_repeat('a', 64),
        ]);
        $resolver = Mockery::mock(RemoteDownloadFileMetadataResolver::class);
        $resolver->shouldReceive('resolve')->times(3)->andReturn([
            'size_bytes' => 1024, 'sha256' => str_repeat('a', 64), 'mime_type' => 'application/octet-stream',
        ]);
        $this->app->instance(RemoteDownloadFileMetadataResolver::class, $resolver);
        $controller = app(LauncherContentController::class);
        $this->assertSame(1, $controller->updates($this->request(), 1)->getData(true)['data'][0]['id']);
        $mods = $controller->mods($this->request(), 1, app(GetLauncherModsAction::class))->getData(true)['data'];
        $this->assertSame(1, $mods[0]['id']);
        $this->assertTrue($mods[0]['compatibility']['is_compatible']);
        foreach (['update', 'mod'] as $type) {
            $result = app(PrepareLauncherContentDownloadAction::class)->execute(
                $this->user(), LauncherDevice::findOrFail(1), 1, $type, 1, 1, Str::uuid()->toString(),
            );
            $this->assertSame($type.'.7z', $result['file_name']);
        }
        $this->assertSame(2, LauncherDownloadSession::count());
    }

    public function test_unpublishing_from_launcher_remains_a_separate_access_control(): void
    {
        $this->retire();
        ProductSaleItem::findOrFail(1)->update(['launcher_enabled' => false]);
        $this->assertSame([], $this->catalog()->getCollection()->modelKeys());
        $this->assertSame([], app(GetUserLibraryAction::class)->execute($this->user())->getCollection()->modelKeys());
        $this->expectException(ModelNotFoundException::class);
        app(GetLauncherCatalogAction::class)->find($this->user(), 1);
    }

    public function test_retired_items_cannot_start_checkout_but_existing_owners_are_still_recognized(): void
    {
        $this->retire();
        $controller = app(LauncherCommerceController::class);
        $this->assertSame(1, $controller->checkout($this->request(), 1)->getData(true)['data']['purchase_access_id']);
        try {
            $controller->checkout($this->request(2), 1);
            $this->fail('An unavailable item must not start a new checkout.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }
    }

    public function test_retired_free_items_keep_valid_claims_but_disallow_new_claims(): void
    {
        DB::table('product_sale_items')->where('id', 1)->update(['launcher_free_claim_enabled' => true, 'base_price' => 0]);
        DB::table('orders')->insert(['id' => 1, 'metadata' => '{"source":"launcher_free_claim"}']);
        DB::table('purchase_accesses')->where('id', 1)->update(['order_id' => 1]);
        $this->retire();
        $this->assertSame([1], $this->catalog()->getCollection()->modelKeys());
        $action = app(ClaimFreeSaleItemAction::class);
        $this->assertSame(1, $action->execute($this->user(), 1, Str::uuid()->toString())->id);
        $detail = LauncherCatalogItemResource::make(app(GetLauncherCatalogAction::class)
            ->find($this->user(2), 1))->resolve($this->request(2));
        $this->assertFalse($detail['actions']['can_claim']);
        $this->expectException(LauncherCommerceException::class);
        $action->execute($this->user(2), 1, Str::uuid()->toString());
    }

    public function test_website_cart_still_rejects_new_purchases_of_retired_items(): void
    {
        $this->retire();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('This item is not purchasable.');
        app(ValidatePurchasableForCartAction::class)->execute(
            new CartOwnerDTO(2, null), new Cart(['user_id' => 2, 'status' => 'active']),
            ProductSaleItem::findOrFail(1), 1,
        );
    }

    public function test_offline_license_still_contains_the_retired_entitlement(): void
    {
        $this->retire();
        // Ephemeral signing keys: never read or replace a developer's real license key.
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $privateKey);
        $publicKey = openssl_pkey_get_details($key)['key'];
        File::shouldReceive('isFile')->andReturn(true);
        File::shouldReceive('get')->andReturnUsing(fn (string $path) => str_contains($path, '-private.pem') ? $privateKey : $publicKey);
        $settings = Mockery::mock(GeneralSettings::class);
        $settings->shouldReceive('get')->with('launcher_emergency_offline_enabled', false)->andReturn(false);
        $this->app->instance(GeneralSettings::class, $settings);
        $license = app(IssueOfflineLicenseAction::class)->execute($this->user(), LauncherDevice::findOrFail(1));
        $payload = json_decode(base64_decode(strtr($license['payload'], '-_', '+/')), true);
        $this->assertSame(1, $payload['entitlements'][0]['product_sale_item_id']);
        $this->assertSame(1, $payload['entitlements'][0]['purchase_access_id']);
    }
}
