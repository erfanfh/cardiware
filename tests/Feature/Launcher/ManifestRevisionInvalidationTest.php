<?php

namespace Tests\Feature\Launcher;

use App\Application\Launcher\Actions\GenerateTemporaryLauncherDownloadUrlAction;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManifestRevisionInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_a_launcher_file_bumps_revision_and_invalidates_old_sessions(): void
    {
        $user = User::factory()->create();
        $now = now();

        $storeId = DB::table('stores')->insertGetId([
            'title' => 'Launcher Test Store',
            'slug' => 'launcher-test-store',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $productId = DB::table('products')->insertGetId([
            'store_id' => $storeId,
            'title' => 'Launcher Test Product',
            'slug' => 'launcher-test-product',
            'type' => 'digital',
            'structure' => 'simple',
            'status' => 'active',
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $saleItemId = DB::table('product_sale_items')->insertGetId([
            'product_id' => $productId,
            'sku' => 'LAUNCHER-REVISION-TEST',
            'base_price' => 1000,
            'status' => 'available',
            'version' => '1.0.0',
            'manifest_revision' => 12,
            'launcher_enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $fileId = DB::table('product_sale_item_files')->insertGetId([
            'product_sale_item_id' => $saleItemId,
            'title' => 'Part 1',
            'file_name' => 'package.7z.001',
            'url' => 'https://downloads.example.test/package.7z.001',
            'size_bytes' => 1024,
            'sha256' => str_repeat('a', 64),
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
                'order_id' => 900001,
                'order_item_id' => 900001,
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
                'name' => 'Test device',
                'platform' => 'windows',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $productDownloadSessionId = Str::uuid()->toString();
        $productSessionId = DB::table('purchase_access_downloads')->insertGetId([
            'purchase_access_id' => $accessId,
            'user_id' => $user->getKey(),
            'product_id' => $productId,
            'product_sale_item_id' => $saleItemId,
            'product_sale_item_file_id' => $fileId,
            'download_session_id' => $productDownloadSessionId,
            'manifest_revision' => 12,
            'file_sha256' => str_repeat('a', 64),
            'file_size_bytes' => 1024,
            'file_name' => 'package.7z.001',
            'session_expires_at' => now()->addHour(),
            'prepare_count' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $oldSignedUrl = app(GenerateTemporaryLauncherDownloadUrlAction::class)->execute(
            purchaseAccessId: $accessId,
            contentType: 'product',
            contentId: $saleItemId,
            fileId: $fileId,
            downloadSessionId: $productDownloadSessionId,
            manifestRevision: 12,
            expiresAt: now()->addHour(),
        );
        $contentSessionId = DB::table('launcher_download_sessions')->insertGetId([
            'purchase_access_id' => $accessId,
            'launcher_device_id' => $deviceId,
            'content_type' => 'mod',
            'content_id' => 77,
            'file_id' => 88,
            'download_session_id' => Str::uuid()->toString(),
            'manifest_revision' => 12,
            'file_sha256' => str_repeat('a', 64),
            'file_size_bytes' => 1024,
            'file_name' => 'package.7z.001',
            'session_expires_at' => now()->addHour(),
            'prepare_count' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        ProductSaleItemFile::query()->findOrFail($fileId)->update([
            'file_name' => 'package-v2.7z.001',
            'sha256' => str_repeat('b', 64),
        ]);

        $this->assertSame(
            13,
            (int) DB::table('product_sale_items')->where('id', $saleItemId)->value('manifest_revision')
        );
        $this->assertNotNull(
            DB::table('purchase_access_downloads')->where('id', $productSessionId)->value('invalidated_at')
        );
        $this->assertNotNull(
            DB::table('launcher_download_sessions')->where('id', $contentSessionId)->value('invalidated_at')
        );
        $this->get($oldSignedUrl)->assertStatus(410);
    }
}
