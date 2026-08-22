<?php

namespace Tests\Feature\Launcher;

use App\Application\Commerce\Actions\Access\PreparePurchaseAccessFileDownloadAction;
use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Models\PurchaseAccessDownload;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class RenewableDownloadSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_lease_is_renewed_without_creating_a_new_download(): void
    {
        config([
            'launcher.download_url_ttl_minutes' => 60,
            'launcher.download_session_lease_minutes' => 10080,
        ]);

        $user = User::factory()->create();
        $now = now();
        $sha256 = str_repeat('a', 64);
        $sessionUuid = Str::uuid()->toString();

        $storeId = DB::table('stores')->insertGetId([
            'title' => 'Renewable Download Store',
            'slug' => 'renewable-download-store',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $productId = DB::table('products')->insertGetId([
            'store_id' => $storeId,
            'title' => 'Large Launcher Package',
            'slug' => 'large-launcher-package',
            'type' => 'digital',
            'structure' => 'simple',
            'status' => 'active',
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $saleItemId = DB::table('product_sale_items')->insertGetId([
            'product_id' => $productId,
            'sku' => 'RENEWABLE-DOWNLOAD-TEST',
            'base_price' => 1000,
            'status' => 'available',
            'version' => '1.0.0',
            'manifest_revision' => 7,
            'launcher_enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $fileId = DB::table('product_sale_item_files')->insertGetId([
            'product_sale_item_id' => $saleItemId,
            'title' => 'Large part',
            'file_name' => 'large-package.7z.001',
            'url' => 'https://downloads.example.test/large-package.7z.001',
            'size_bytes' => 1024,
            'sha256' => $sha256,
            'part_number' => 1,
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Schema::disableForeignKeyConstraints();
        try {
            $accessId = DB::table('purchase_accesses')->insertGetId([
                'user_id' => $user->getKey(),
                'order_id' => 910001,
                'order_item_id' => 910001,
                'product_id' => $productId,
                'product_sale_item_id' => $saleItemId,
                'status' => 'active',
                'downloads_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $deviceId = DB::table('launcher_devices')->insertGetId([
                'user_id' => $user->getKey(),
                'device_id' => Str::uuid()->toString(),
                'name' => 'Renewal test device',
                'platform' => 'windows',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $downloadId = DB::table('purchase_access_downloads')->insertGetId([
            'purchase_access_id' => $accessId,
            'user_id' => $user->getKey(),
            'launcher_device_id' => $deviceId,
            'product_id' => $productId,
            'product_sale_item_id' => $saleItemId,
            'product_sale_item_file_id' => $fileId,
            'download_session_id' => $sessionUuid,
            'manifest_revision' => 7,
            'file_sha256' => $sha256,
            'file_size_bytes' => 1024,
            'file_name' => 'large-package.7z.001',
            'file_disk' => 'remote_url',
            'file_path' => 'https://downloads.example.test/large-package.7z.001',
            'session_expires_at' => now()->subMinute(),
            'prepare_count' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $resolver = Mockery::mock(RemoteDownloadFileMetadataResolver::class);
        $resolver->shouldReceive('resolve')->once()->andReturn([
            'size_bytes' => 1024,
            'sha256' => $sha256,
            'mime_type' => 'application/octet-stream',
        ]);
        $this->app->instance(RemoteDownloadFileMetadataResolver::class, $resolver);

        $result = app(PreparePurchaseAccessFileDownloadAction::class)->execute(
            user: $user,
            device: LauncherDevice::query()->findOrFail($deviceId),
            purchaseAccessId: $accessId,
            fileId: $fileId,
            downloadSessionId: $sessionUuid,
            expectedManifestRevision: 7,
            expectedFileName: 'large-package.7z.001',
            expectedSizeBytes: 1024,
            expectedSha256: $sha256,
        );

        $download = PurchaseAccessDownload::query()->findOrFail($downloadId);
        $this->assertSame($sessionUuid, $result['download_session_id']);
        $this->assertTrue($result['reused_session']);
        $this->assertSame(2, $download->prepare_count);
        $this->assertTrue($download->session_expires_at->isAfter(now()->addDays(6)));
        $this->assertSame(1, PurchaseAccessDownload::query()->count());
        $this->assertTrue(PurchaseAccess::query()->findOrFail($accessId)->isUsableForLauncher());
    }
}
