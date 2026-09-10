<?php

namespace Tests\Feature\Launcher;

use App\Application\Shared\Cache\CacheKey;
use App\Domain\Launcher\Models\LauncherRelease;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class LauncherBootstrapCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
        Schema::dropIfExists('launcher_releases');
        Schema::create('launcher_releases', function (Blueprint $table): void {
            $table->id();
            $table->string('platform', 30);
            $table->string('channel', 30);
            $table->string('version', 50);
            $table->string('minimum_supported_version', 50)->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_enabled')->default(false);
            $table->text('download_url');
            $table->string('file_name');
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->text('signature')->nullable();
            $table->char('signature_key_id', 64)->nullable();
            $table->text('release_notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        $this->mock(GeneralSettings::class, function (MockInterface $mock): void {
            $mock->shouldReceive('get')
                ->with('launcher_maintenance_enabled', Mockery::any())
                ->andReturn(false);
            $mock->shouldReceive('get')
                ->with('launcher_maintenance_message', Mockery::any())
                ->andReturn(null);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('launcher_releases');
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_release_snapshot_is_reused_while_server_time_stays_dynamic(): void
    {
        $this->travelTo(Carbon::parse('2026-09-11 10:00:00 UTC'));
        $this->createRelease('1.2.0', now()->subMinute());

        $firstResponse = $this->getBootstrap('1.0.0');

        $firstResponse->assertOk()
            ->assertJsonPath('data.latest_version', '1.2.0')
            ->assertJsonPath('data.server_time', now()->toISOString());

        $cacheKey = CacheKey::launcherBootstrapReleases('windows', 'stable');
        $this->assertTrue(Cache::has($cacheKey));

        $launcherQueries = [];
        DB::listen(static function (QueryExecuted $query) use (&$launcherQueries): void {
            if (str_contains(strtolower($query->sql), 'launcher_releases')) {
                $launcherQueries[] = $query->sql;
            }
        });

        $this->travel(2)->seconds();

        $secondResponse = $this->getBootstrap('1.0.0');

        $secondResponse->assertOk()
            ->assertJsonPath('data.latest_version', '1.2.0')
            ->assertJsonPath('data.server_time', now()->toISOString());
        $this->assertSame([], $launcherQueries);

        $this->travelBack();
    }

    public function test_release_changes_invalidate_the_cached_snapshot(): void
    {
        $release = $this->createRelease('1.2.0', now()->subMinute());

        $this->getBootstrap('1.0.0')
            ->assertOk()
            ->assertJsonPath('data.latest_version', '1.2.0');

        $cacheKey = CacheKey::launcherBootstrapReleases('windows', 'stable');
        $this->assertTrue(Cache::has($cacheKey));

        $release->update(['version' => '1.3.0']);

        $this->assertFalse(Cache::has($cacheKey));
        $this->getBootstrap('1.0.0')
            ->assertOk()
            ->assertJsonPath('data.latest_version', '1.3.0');
    }

    public function test_creating_and_deleting_releases_invalidate_the_cached_snapshot(): void
    {
        $cacheKey = CacheKey::launcherBootstrapReleases('windows', 'stable');

        $this->getBootstrap('1.0.0')->assertOk();
        $this->assertTrue(Cache::has($cacheKey));

        $release = $this->createRelease('1.2.0', now()->subMinute());

        $this->assertFalse(Cache::has($cacheKey));
        $this->getBootstrap('1.0.0')
            ->assertOk()
            ->assertJsonPath('data.latest_version', '1.2.0');
        $this->assertTrue(Cache::has($cacheKey));

        $release->delete();

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_scheduled_release_becomes_visible_without_rebuilding_the_cache(): void
    {
        $this->travelTo(Carbon::parse('2026-09-11 10:00:00 UTC'));
        $this->createRelease('1.2.0', now()->subMinute());
        $this->createRelease('1.3.0', now()->addMinute());

        $this->getBootstrap('1.0.0')
            ->assertOk()
            ->assertJsonPath('data.latest_version', '1.2.0');

        $cacheKey = CacheKey::launcherBootstrapReleases('windows', 'stable');
        $cachedSnapshot = Cache::get($cacheKey);
        $this->assertCount(2, $cachedSnapshot);

        $this->travel(2)->minutes();

        $this->getBootstrap('1.0.0')
            ->assertOk()
            ->assertJsonPath('data.latest_version', '1.3.0');
        $this->assertSame($cachedSnapshot, Cache::get($cacheKey));

        $this->travelBack();
    }

    private function getBootstrap(string $version): TestResponse
    {
        return $this->getJson(route('api.v1.launcher.bootstrap', [
            'version' => $version,
            'platform' => 'windows',
            'channel' => 'stable',
        ]));
    }

    private function createRelease(string $version, Carbon $publishedAt): LauncherRelease
    {
        return LauncherRelease::query()->create([
            'platform' => 'windows',
            'channel' => 'stable',
            'version' => $version,
            'minimum_supported_version' => '1.0.0',
            'is_mandatory' => false,
            'is_enabled' => true,
            'download_url' => "https://downloads.example.com/launcher-{$version}.zip",
            'file_name' => "launcher-{$version}.zip",
            'size_bytes' => 1024,
            'sha256' => str_repeat('a', 64),
            'published_at' => $publishedAt,
        ]);
    }
}
