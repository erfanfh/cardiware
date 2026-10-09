<?php

namespace Tests\Feature\Download;

use App\Infrastructure\Support\Counters\DownloadClickCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\Support\CreatesDownloadEntries;
use Tests\TestCase;

class DownloadClickCounterTest extends TestCase
{
    use CreatesDownloadEntries, RefreshDatabase;

    private ?Connection $redis = null;

    private string $prefix;

    private DownloadClickCounter $counter;

    protected function setUp(): void
    {
        parent::setUp();

        if (! getenv('DOWNLOAD_COUNTER_TEST_REDIS_PORT')) {
            $this->markTestSkipped('Set DOWNLOAD_COUNTER_TEST_REDIS_PORT to run isolated Redis integration tests.');
        }

        $this->prefix = 'download-counter-test:'.Str::random(16).':';
        config([
            'database.redis.client' => 'predis',
            'database.redis.options.prefix' => $this->prefix,
            'database.redis.download_counters' => [
                'host' => '127.0.0.1', 'port' => (int) getenv('DOWNLOAD_COUNTER_TEST_REDIS_PORT'),
                'database' => 0, 'timeout' => 1, 'read_write_timeout' => 1,
            ],
        ]);
        $this->redis = Redis::connection('download_counters');
        $this->redis->ping();
        $this->counter = app(DownloadClickCounter::class);
    }

    protected function tearDown(): void
    {
        try {
            if ($this->redis !== null) {
                // Remove only this test's random prefix; never flush a shared Redis database.
                $client = $this->redis->client();
                $keys = $client->executeRaw(['KEYS', $this->prefix.'*']);
                if ($keys !== []) {
                    $client->executeRaw(['DEL', ...$keys]);
                }
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_same_ip_is_counted_once_per_link_with_an_expiring_dedup_key(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $this->counter->record(1, '192.0.2.10');
        }
        $this->counter->record(1, '192.0.2.11');
        $this->counter->record(2, '192.0.2.10');

        $this->assertSame(2, $this->counter->pendingCount(1));
        $this->assertSame(1, $this->counter->pendingCount(2));
        $visitor = hash_hmac('sha256', '192.0.2.10', config('app.key'));
        $ttl = $this->redis->ttl('download_clicks:seen:1:'.$visitor);
        $this->assertGreaterThan(1790, $ttl);
        $this->assertLessThanOrEqual(1800, $ttl);

        $this->redis->expire('download_clicks:seen:1:'.$visitor, 0);
        $this->counter->record(1, '192.0.2.10');
        $this->assertSame(3, $this->counter->pendingCount(1));
    }

    public function test_browser_click_posts_are_buffered_and_transferred_once(): void
    {
        $entry = $this->createDownloadEntry();
        $clickUrl = URL::signedRoute('downloads.entry.track', ['publicId' => $entry->public_id], absolute: false);
        for ($i = 0; $i < 3; $i++) {
            $this->call('POST', $clickUrl, server: ['REMOTE_ADDR' => '192.0.2.10'])->assertNoContent();
        }
        $this->assertSame(0, $entry->fresh()->download_click_count);
        $this->assertSame(1, $this->counter->pendingCount($entry->id));
        $this->artisan('app:transfer-download-clicks-to-database')->assertSuccessful();
        $this->assertSame(1, $entry->fresh()->download_click_count);
        $this->assertSame([], $this->counter->pendingEntryIds(10));
    }

    public function test_clicks_arriving_during_sql_transfer_remain_buffered(): void
    {
        $entry = $this->createDownloadEntry(['download_click_count' => 10]);
        $this->counter->record($entry->id, '192.0.2.10');
        $added = false;
        DB::listen(function ($query) use ($entry, &$added) {
            if (! $added && str_starts_with(strtolower($query->sql), 'update "download_entries"')) {
                $added = true;
                $this->counter->record($entry->id, '192.0.2.11');
            }
        });

        $this->artisan('app:transfer-download-clicks-to-database')->assertSuccessful();
        $this->assertTrue($added);
        $this->assertSame(11, $entry->fresh()->download_click_count);
        $this->assertSame(1, $this->counter->pendingCount($entry->id));
        $this->artisan('downloads:flush-clicks')->assertSuccessful();
        $this->assertSame(12, $entry->fresh()->download_click_count);
        $this->assertSame([], $this->counter->pendingEntryIds(10));
        $this->artisan('app:transfer-download-clicks-to-database')->assertSuccessful();
        $this->assertSame(12, $entry->fresh()->download_click_count);
    }

    public function test_sql_failure_keeps_buffered_counts_and_dirty_marker_for_retry(): void
    {
        $entry = $this->createDownloadEntry();
        $this->counter->record($entry->id, '192.0.2.10');
        DB::partialMock()->shouldReceive('table')->with('download_entries')->once()
            ->andThrow(new RuntimeException('SQL unavailable'));

        $this->artisan('app:transfer-download-clicks-to-database')->assertFailed();
        $this->assertSame(1, $this->counter->pendingCount($entry->id));
        $this->assertSame([$entry->id], $this->counter->pendingEntryIds(10));
        $this->assertSame(0, $entry->fresh()->download_click_count);
    }

    public function test_legacy_and_current_buffers_are_transferred_by_the_same_command(): void
    {
        $entry = $this->createDownloadEntry(['download_click_count' => 5]);
        $this->redis->set('download_entry_clicks:'.$entry->id, 3);
        $this->redis->sadd('download_entry_clicks:dirty', $entry->id);
        $this->redis->set('counter:download_clicks:DownloadEntry:'.$entry->id, 2);
        $this->redis->sadd('counter:download_clicks:dirty', 'DownloadEntry:'.$entry->id);

        $this->artisan('downloads:flush-clicks')->assertSuccessful();
        $this->assertSame(10, $entry->fresh()->download_click_count);
        $this->assertSame([], $this->counter->pendingEntryIds(10));
        $this->assertNull($this->redis->get('download_entry_clicks:'.$entry->id));
    }

    public function test_existing_flush_lock_prevents_a_second_worker_from_consuming_counts(): void
    {
        $entry = $this->createDownloadEntry();
        $this->counter->record($entry->id, '192.0.2.10');
        $lock = Cache::store('download_counters')->lock('downloads:flush-clicks', 600);
        $this->assertTrue($lock->get());
        try {
            $this->artisan('downloads:flush-clicks')->assertSuccessful();
            $this->assertSame(0, $entry->fresh()->download_click_count);
            $this->assertSame(1, $this->counter->pendingCount($entry->id));
        } finally {
            $lock->release();
        }
        $this->artisan('downloads:flush-clicks')->assertSuccessful();
        $this->assertSame(1, $entry->fresh()->download_click_count);
    }

    public function test_batch_limit_and_missing_entries_are_handled_without_losing_other_counts(): void
    {
        $first = $this->createDownloadEntry();
        $second = $this->createDownloadEntry();
        $this->counter->record($first->id, '192.0.2.10');
        $this->counter->record($second->id, '192.0.2.10');
        $this->artisan('app:transfer-download-clicks-to-database', ['--limit' => 1])->assertSuccessful();
        $this->assertSame(1, $first->fresh()->download_click_count + $second->fresh()->download_click_count);
        $this->assertCount(1, $this->counter->pendingEntryIds(10));

        $this->counter->record(999999, '192.0.2.10');
        $this->artisan('app:transfer-download-clicks-to-database')->assertSuccessful();
        $this->assertSame(1, $first->fresh()->download_click_count);
        $this->assertSame(1, $second->fresh()->download_click_count);
        $this->assertSame([], $this->counter->pendingEntryIds(10));
    }
}
