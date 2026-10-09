<?php

namespace App\Infrastructure\Support\Counters;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class DownloadClickCounter
{
    private const DIRTY_KEY = 'counter:download_clicks:dirty';

    private const LEGACY_DIRTY_KEY = 'download_entry_clicks:dirty';

    public function record(int $entryId, string $ip): void
    {
        // Approximate click counts, independent of browser sessions and cookies.
        $visitor = hash_hmac('sha256', $ip, (string) config('app.key'));
        $ttl = max(1, (int) config('cache.download_clicks_dedup_minutes', 30)) * 60;

        $this->connection()->eval(<<<'LUA'
            if redis.call('EXISTS', KEYS[1]) == 1 then
                return 0
            end
            redis.call('INCR', KEYS[2])
            redis.call('SADD', KEYS[3], ARGV[2])
            redis.call('SET', KEYS[1], '1', 'EX', ARGV[1])
            return 1
            LUA,
            3,
            "download_clicks:seen:{$entryId}:{$visitor}",
            $this->counterKey($entryId),
            self::DIRTY_KEY,
            $ttl,
            "DownloadEntry:{$entryId}",
        );
    }

    /** @return list<int> */
    public function pendingEntryIds(int $limit): array
    {
        if ($limit < 1) {
            return [];
        }

        $ids = [];
        // Drain the older implementation too, without abandoning existing buffered counts.
        foreach ($this->connection()->srandmember(self::LEGACY_DIRTY_KEY, $limit) as $id) {
            if (ctype_digit((string) $id) && (int) $id > 0) {
                $ids[(int) $id] = (int) $id;
            }
        }

        foreach ($this->connection()->srandmember(self::DIRTY_KEY, $limit) as $dirty) {
            if (preg_match('/^DownloadEntry:([1-9][0-9]*)$/', $dirty, $matches)) {
                $ids[(int) $matches[1]] = (int) $matches[1];
            }
        }

        return array_slice(array_values($ids), 0, $limit);
    }

    public function pendingCount(int $entryId): int
    {
        return (int) $this->connection()->eval(<<<'LUA'
            local legacy = tonumber(redis.call('GET', KEYS[2]) or '0')
            if legacy > 0 then
                redis.call('INCRBY', KEYS[1], legacy)
                redis.call('DEL', KEYS[2])
                redis.call('SADD', KEYS[3], ARGV[1])
            end
            redis.call('SREM', KEYS[4], ARGV[2])
            return tonumber(redis.call('GET', KEYS[1]) or '0')
            LUA,
            4,
            $this->counterKey($entryId),
            "download_entry_clicks:{$entryId}",
            self::DIRTY_KEY,
            self::LEGACY_DIRTY_KEY,
            "DownloadEntry:{$entryId}",
            $entryId,
        );
    }

    public function acknowledge(int $entryId, int $count): void
    {
        // Subtract only the snapshot saved to SQL; concurrent clicks stay buffered.
        $this->connection()->eval(<<<'LUA'
            local current = tonumber(redis.call('GET', KEYS[1]) or '0')
            if current > tonumber(ARGV[1]) then
                redis.call('DECRBY', KEYS[1], ARGV[1])
                redis.call('SADD', KEYS[2], ARGV[2])
            else
                redis.call('DEL', KEYS[1])
                redis.call('SREM', KEYS[2], ARGV[2])
            end
            return 1
            LUA,
            2,
            $this->counterKey($entryId),
            self::DIRTY_KEY,
            $count,
            "DownloadEntry:{$entryId}",
        );
    }

    private function counterKey(int $entryId): string
    {
        return "counter:download_clicks:DownloadEntry:{$entryId}";
    }

    private function connection(): Connection
    {
        return Redis::connection('download_counters');
    }
}
