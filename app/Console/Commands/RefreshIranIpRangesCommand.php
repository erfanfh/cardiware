<?php

namespace App\Console\Commands;

use App\Application\Launcher\Support\IranIpRanges;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Refreshes the Iranian IP list used by the launcher VPN check from RIPE NCC.
 * Keeps the previous file whenever the download fails or looks wrong.
 */
final class RefreshIranIpRangesCommand extends Command
{
    protected $signature = 'launcher:refresh-iran-ip-ranges';

    protected $description = 'Download the current Iranian IP ranges (RIPE NCC) for the launcher VPN check';

    public function handle(): int
    {
        try {
            $response = Http::timeout(60)->retry(2, 5000)->get(
                'https://stat.ripe.net/data/country-resource-list/data.json',
                ['resource' => 'IR', 'v4_format' => 'prefix'],
            );
        } catch (\Throwable $exception) {
            $this->error('Download failed, keeping the current list: ' . $exception->getMessage());
            return self::FAILURE;
        }

        $resources = $response->json('data.resources');
        $v4 = array_values(array_filter((array)($resources['ipv4'] ?? []), 'is_string'));
        $v6 = array_values(array_filter((array)($resources['ipv6'] ?? []), 'is_string'));

        // Iran has ~2000 IPv4 prefixes; anything far below that is a broken response, and
        // writing it would mark real Iranian customers as "VPN".
        if (!$response->successful() || count($v4) < 500) {
            $this->error('Unexpected response (' . count($v4) . ' IPv4 prefixes), keeping the current list.');
            return self::FAILURE;
        }

        $path = IranIpRanges::path();
        @mkdir(dirname($path), 0775, true);
        $body = '# Iranian IP ranges, RIPE NCC country-resource-list IR, ' . now()->toDateTimeString() . "\n"
            . implode("\n", array_merge($v4, $v6)) . "\n";
        file_put_contents($path . '.tmp', $body);
        rename($path . '.tmp', $path);

        $this->info('Saved ' . count($v4) . ' IPv4 and ' . count($v6) . ' IPv6 ranges.');
        return self::SUCCESS;
    }
}
