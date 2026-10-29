<?php

namespace Tests\Feature\Cache;

use App\Application\Shared\Cache\Observers\PublicReadCacheObserver;
use App\Application\Shared\Cache\PublicReadCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicReadCacheTransactionTest extends TestCase
{
    public function test_commit_clears_data_repopulated_between_the_write_and_the_commit(): void
    {
        Schema::create('cache_transaction_records', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });
        CacheTransactionRecord::observe(PublicReadCacheObserver::class);
        $post = CacheTransactionRecord::query()->create(['title' => 'Original']);
        $cache = app(PublicReadCache::class);
        $read = fn () => $cache->remember('transaction-check', [], [CacheTransactionRecord::class], fn () => CacheTransactionRecord::findOrFail($post->id)->title);
        self::assertSame('Original', $read());

        DB::transaction(function () use ($cache, $post): void {
            $post->update(['title' => 'Committed title']);

            // Simulate another connection filling the shared cache before this commit.
            $cache->remember('transaction-check', [], [CacheTransactionRecord::class], fn () => 'Original');
        });

        self::assertSame('Committed title', $read());
    }
}

class CacheTransactionRecord extends Model
{
    protected $table = 'cache_transaction_records';

    protected $guarded = [];
}
