<?php

namespace Tests\Feature\Launcher;

use App\Application\Commerce\Actions\Access\PreparePurchaseAccessFileDownloadAction;
use App\Application\Launcher\Actions\GenerateTemporaryLauncherDownloadUrlAction;
use App\Application\Launcher\Actions\PrepareLauncherContentDownloadAction;
use App\Domain\Commerce\Models\PurchaseAccessDownload;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherDownloadSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class LauncherDownloadRedirectTest extends TestCase
{
    use RefreshDatabase;

    private const SOURCE_URL = 'https://downloads.example.test/package.7z.001';

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        config([
            'launcher.direct_download_enabled' => false,
            'launcher.secure_link_secret' => '',
            'launcher.allowed_download_hosts' => ['downloads.example.test'],
        ]);
    }

    public static function downloadRequests(): array
    {
        $cases = [];
        foreach (['product', 'update', 'mod'] as $type) {
            foreach (['GET', 'HEAD'] as $method) {
                foreach ([null, 'bytes=512-1023', 'bytes=-256'] as $range) {
                    $cases["{$type} {$method} {$range}"] = [$type, $method, $range];
                }
            }
        }

        return $cases;
    }

    #[DataProvider('downloadRequests')]
    public function test_existing_signed_urls_redirect_without_streaming_or_consuming_quota(
        string $type,
        string $method,
        ?string $range,
    ): void {
        $fixture = $this->createDownload($type);
        $sessionBefore = $fixture['session']->fresh()->getAttributes();

        $response = $this->call($method, $fixture['url'], server: $range === null ? [] : ['HTTP_RANGE' => $range]);

        $response->assertStatus(307)->assertRedirect(self::SOURCE_URL);
        $response->assertHeader('Cache-Control', 'max-age=0, no-store, private');
        $response->assertHeaderMissing('Content-Range');
        $this->assertNotInstanceOf(StreamedResponse::class, $response->baseResponse);
        $this->assertSame($sessionBefore, $fixture['session']->fresh()->getAttributes());
        $this->assertSame(1, $fixture['session']->newQuery()->count());
    }

    public static function preparationModes(): array
    {
        return [
            ['product', false], ['product', true],
            ['update', false], ['update', true],
            ['mod', false], ['mod', true],
        ];
    }

    #[DataProvider('preparationModes')]
    public function test_prepare_renews_the_same_session_and_preserves_both_url_modes(string $type, bool $direct): void
    {
        config(['launcher.direct_download_enabled' => $direct]);
        $fixture = $this->createDownload($type);
        $session = $fixture['session'];
        $session->forceFill(['session_expires_at' => now()->subMinute()])->save();
        $user = User::query()->findOrFail($fixture['userId']);
        $device = LauncherDevice::query()->findOrFail($fixture['deviceId']);

        $result = $type === 'product'
            ? app(PreparePurchaseAccessFileDownloadAction::class)->execute(
                $user, $device, $fixture['accessId'], $fixture['fileId'], $session->download_session_id,
            )
            : app(PrepareLauncherContentDownloadAction::class)->execute(
                $user, $device, $fixture['accessId'], $type, $fixture['contentId'],
                $fixture['fileId'], $session->download_session_id,
            );

        $this->assertSame($session->download_session_id, $result['download_session_id']);
        $this->assertSame('package.7z.001', $result['file_name']);
        $this->assertSame(1024, $result['size_bytes']);
        $this->assertSame(str_repeat('a', 64), $result['sha256']);
        $this->assertSame(7, $result['manifest_revision']);
        $this->assertTrue($result['reused_session']);
        $this->assertSame(1, $session->newQuery()->count());
        $this->assertSame(2, $session->fresh()->prepare_count);
        $this->assertTrue($session->fresh()->session_expires_at->isAfter(now()->addDays(6)));
        if ($direct && $type !== 'product') {
            $this->assertSame(self::SOURCE_URL, $result['url']);
        } else {
            $this->get($result['url'])->assertStatus(307)->assertRedirect(self::SOURCE_URL);
        }
    }

    public function test_redirect_uses_existing_download_host_signing_configuration(): void
    {
        $this->freezeTime();
        config(['launcher.secure_link_secret' => 'test-secret', 'launcher.secure_link_ttl_minutes' => 240]);
        $fixture = $this->createDownload('product');
        $source = 'https://downloads.example.test/folder%20name/package.7z.001?mirror=2';
        DB::table('product_sale_item_files')->where('id', $fixture['fileId'])->update(['url' => $source]);

        $response = $this->get($fixture['url'])->assertStatus(307);
        $parts = parse_url($response->headers->get('Location'));
        parse_str($parts['query'], $query);
        $this->assertSame('downloads.example.test', $parts['host']);
        $this->assertSame('/folder%20name/package.7z.001', $parts['path']);
        $this->assertSame('2', $query['mirror']);
        $this->assertSame((string) now()->addMinutes(240)->timestamp, $query['expires']);
        $token = substr(hash_hmac('sha256', 'launcher-download|' . $fixture['userId'], 'test-secret'), 0, 16);
        $this->assertSame($token, $query['u']);
        $expected = rtrim(strtr(base64_encode(md5(
            'test-secret' . $query['expires'] . '/folder name/package.7z.001' . $token,
            true,
        )), '+/', '-_'), '=');
        $this->assertSame($expected, $query['md5']);
    }

    public static function rejectedDownloads(): array
    {
        return [
            'expired lease' => ['session', ['session_expires_at' => '2000-01-01'], 410],
            'invalidated session' => ['session', ['invalidated_at' => '2000-01-01'], 410],
            'changed checksum' => ['session', ['file_sha256' => str_repeat('b', 64)], 410],
            'changed size' => ['session', ['file_size_bytes' => 2048], 410],
            'changed filename' => ['session', ['file_name' => 'old.7z.001'], 410],
            'changed manifest' => ['product_sale_items', ['manifest_revision' => 8], 410],
            'revoked device' => ['launcher_devices', ['revoked_at' => '2000-01-01'], 410],
            'switched device' => ['users', ['active_device_slot' => 2], 410],
            'changed key' => ['users', ['first_device_public_key' => base64_encode('other-key')], 410],
            'suspended account' => ['users', ['launcher_suspended_at' => '2000-01-01'], 403],
            'expired access' => ['purchase_accesses', ['expires_at' => '2000-01-01'], 403],
            'disabled file' => ['product_sale_item_files', ['is_active' => false], 404],
        ];
    }

    #[DataProvider('rejectedDownloads')]
    public function test_access_checks_still_run_before_redirecting(string $table, array $changes, int $status): void
    {
        $fixture = $this->createDownload('product');
        if ($table === 'session') {
            $fixture['session']->forceFill($changes)->save();
        } else {
            DB::table($table)->update($changes);
        }

        $this->getJson($fixture['url'])->assertStatus($status)->assertHeaderMissing('Location');
    }

    public function test_tampered_signature_is_rejected(): void
    {
        $fixture = $this->createDownload('product');
        $this->getJson($fixture['url'] . '&unexpected=1')->assertForbidden()->assertHeaderMissing('Location');
    }

    public function test_unapproved_download_host_is_rejected(): void
    {
        $fixture = $this->createDownload('product');
        config(['launcher.allowed_download_hosts' => ['other.example.test']]);
        $this->getJson($fixture['url'])->assertStatus(503)->assertHeaderMissing('Location');
    }

    private function createDownload(string $type): array
    {
        $user = User::factory()->create([
            'active_device_slot' => 1,
            'first_device_public_key' => base64_encode('download-test-public-key'),
        ]);
        $userId = $user->getKey();
        $now = now();
        $storeId = DB::table('stores')->insertGetId([
            'title' => 'Download Store', 'slug' => 'download-store', 'status' => 'active',
        ]);
        $productId = DB::table('products')->insertGetId([
            'store_id' => $storeId, 'title' => 'Download Product', 'slug' => 'download-product',
            'type' => 'digital', 'structure' => 'simple', 'status' => 'active', 'published_at' => $now,
        ]);
        $saleItemId = DB::table('product_sale_items')->insertGetId([
            'product_id' => $productId, 'sku' => 'DOWNLOAD-TEST', 'base_price' => 1000,
            'status' => 'available', 'version' => '1.0.0', 'manifest_revision' => 7, 'launcher_enabled' => true,
        ]);
        Schema::disableForeignKeyConstraints();
        try {
            $accessId = DB::table('purchase_accesses')->insertGetId([
                'user_id' => $userId, 'order_id' => 910001, 'order_item_id' => 910001,
                'product_id' => $productId, 'product_sale_item_id' => $saleItemId,
                'status' => 'active',
            ]);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
        $deviceId = DB::table('launcher_devices')->insertGetId([
            'user_id' => $userId, 'device_id' => Str::uuid()->toString(), 'name' => 'Download device',
            'platform' => 'windows', 'device_slot' => 1,
            'public_key_fingerprint' => hash('sha256', 'download-test-public-key'),
        ]);
        $contentId = match ($type) {
            'product' => $saleItemId,
            'update' => DB::table('launcher_updates')->insertGetId([
                'product_sale_item_id' => $saleItemId, 'version' => '1.1.0', 'title' => 'Update',
                'is_enabled' => true, 'published_at' => $now,
            ]),
            'mod' => DB::table('launcher_mods')->insertGetId([
                'product_sale_item_id' => $saleItemId, 'version' => '1.0.0', 'name' => 'Mod',
                'slug' => 'download-mod', 'is_enabled' => true, 'published_at' => $now,
            ]),
        };
        [$fileTable, $ownerKey] = match ($type) {
            'product' => ['product_sale_item_files', 'product_sale_item_id'],
            'update' => ['launcher_update_files', 'launcher_update_id'],
            'mod' => ['launcher_mod_files', 'launcher_mod_id'],
        };
        $fileId = DB::table($fileTable)->insertGetId([
            $ownerKey => $contentId, 'title' => 'Package', 'file_name' => 'package.7z.001',
            'url' => self::SOURCE_URL, 'size_bytes' => 1024, 'sha256' => str_repeat('a', 64),
        ]);
        $sessionId = Str::uuid()->toString();
        $attributes = [
            'purchase_access_id' => $accessId, 'launcher_device_id' => $deviceId,
            'download_session_id' => $sessionId, 'manifest_revision' => 7,
            'file_sha256' => str_repeat('a', 64), 'file_size_bytes' => 1024,
            'file_name' => 'package.7z.001', 'session_expires_at' => now()->addDay(), 'prepare_count' => 1,
        ];
        $session = $type === 'product'
            ? PurchaseAccessDownload::query()->create($attributes + [
                'user_id' => $userId, 'product_id' => $productId, 'product_sale_item_id' => $saleItemId,
                'product_sale_item_file_id' => $fileId, 'file_disk' => 'remote_url', 'file_path' => self::SOURCE_URL,
            ])
            : LauncherDownloadSession::query()->create($attributes + [
                'content_type' => $type, 'content_id' => $contentId, 'file_id' => $fileId,
            ]);
        $url = app(GenerateTemporaryLauncherDownloadUrlAction::class)->execute(
            $accessId, $type, $contentId, $fileId, $sessionId, 7, now()->addHour(),
        );

        return compact('url', 'session', 'userId', 'deviceId', 'accessId', 'contentId', 'fileId');
    }
}
