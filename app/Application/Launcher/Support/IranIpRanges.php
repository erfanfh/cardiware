<?php

namespace App\Application\Launcher\Support;

use Illuminate\Support\Facades\Cache;

/**
 * "Is this client IP Iranian?" for the launcher's VPN warning/block.
 *
 * Source: RIPE NCC's country resource list for IR (the registry that allocates Iranian
 * address space), stored as one CIDR per line in storage/app/private/launcher/
 * iran-ip-ranges.txt and refreshed weekly by `launcher:refresh-iran-ip-ranges`.
 *
 * Fails OPEN: with no list (or an unparsable IP) the answer is null = "unknown", and the
 * caller must treat that as "do not warn / do not block". A missing file must never lock
 * every customer out of the launcher.
 */
final class IranIpRanges
{
    public static function path(): string
    {
        return storage_path('app/private/launcher/iran-ip-ranges.txt');
    }

    /**
     * true = Iranian, false = outside Iran, null = unknown (no list / bad IP).
     *
     * $extraRanges: admin-entered CIDRs (one per line) that count as Iranian on top of the
     * RIPE list. RIPE's country list is incomplete -- some Iranian providers' blocks are
     * registered elsewhere (e.g. 91.98.0.0/16 is missing) -- so a customer wrongly seen as
     * "VPN" is fixed by adding their provider's range in the panel, no deploy needed.
     */
    public function isIranian(?string $ip, string $extraRanges = ''): ?bool
    {
        $ip = trim((string)$ip);
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        // Loopback/private/reserved addresses are never a VPN exit; they only show up when
        // the request did not come over the internet (local tests, a proxy not trusted).
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return true;
        }

        foreach (preg_split('/\R/', $extraRanges) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '' && $this->cidrContains($line, $ip)) {
                return true;
            }
        }

        $ranges = $this->ranges();
        if ($ranges === null) {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $value = ip2long($ip);
            // Binary search over sorted, non-overlapping-by-start [start, end] pairs.
            $low = 0;
            $high = count($ranges['v4']) - 1;
            while ($low <= $high) {
                $mid = intdiv($low + $high, 2);
                [$start, $end] = $ranges['v4'][$mid];
                if ($value < $start) {
                    $high = $mid - 1;
                } elseif ($value > $end) {
                    $low = $mid + 1;
                } else {
                    return true;
                }
            }

            return false;
        }

        $binary = inet_pton($ip);
        foreach ($ranges['v6'] as [$prefix, $bits]) {
            if ($this->prefixMatches($binary, $prefix, $bits)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{v4: list<array{int,int}>, v6: list<array{string,int}>}|null */
    private function ranges(): ?array
    {
        $path = self::path();
        if (!is_file($path)) {
            return null;
        }

        return Cache::remember('launcher_iran_ip_ranges:' . filemtime($path), 86400, function () use ($path): ?array {
            $v4 = [];
            $v6 = [];
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || !str_contains($line, '/')) {
                    continue;
                }
                [$network, $bits] = explode('/', $line, 2);
                $bits = (int)$bits;
                if (filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false && $bits >= 0 && $bits <= 32) {
                    $start = ip2long($network) & ($bits === 0 ? 0 : (~0 << (32 - $bits)) & 0xFFFFFFFF);
                    $end = $start | ((1 << (32 - $bits)) - 1);
                    $v4[] = [$start, $end];
                } elseif (filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false && $bits >= 0 && $bits <= 128) {
                    $v6[] = [inet_pton($network), $bits];
                }
            }
            if (count($v4) < 100) {
                return null;   // a truncated/garbage file must not mark everyone as foreign
            }
            usort($v4, fn ($a, $b) => $a[0] <=> $b[0]);

            // Merge overlaps so the binary search above is exact.
            $merged = [];
            foreach ($v4 as [$start, $end]) {
                $last = count($merged) - 1;
                if ($last >= 0 && $start <= $merged[$last][1] + 1) {
                    $merged[$last][1] = max($merged[$last][1], $end);
                } else {
                    $merged[] = [$start, $end];
                }
            }

            return ['v4' => $merged, 'v6' => $v6];
        });
    }

    /** One CIDR ("1.2.3.0/24", "2001:db8::/32") or a single address; false if malformed. */
    public function cidrContains(string $cidr, string $ip): bool
    {
        [$network, $bits] = str_contains($cidr, '/') ? explode('/', $cidr, 2) : [$cidr, null];
        $network = trim($network);
        $networkBinary = @inet_pton($network);
        $ipBinary = @inet_pton($ip);
        if ($networkBinary === false || $ipBinary === false || strlen($networkBinary) !== strlen($ipBinary)) {
            return false;
        }
        $maxBits = strlen($networkBinary) * 8;
        $bits = $bits === null ? $maxBits : (int)$bits;
        if ($bits < 0 || $bits > $maxBits) {
            return false;
        }

        return $this->prefixMatches($ipBinary, $networkBinary, $bits);
    }

    private function prefixMatches(string $address, string $prefix, int $bits): bool
    {
        $fullBytes = intdiv($bits, 8);
        if (strncmp($address, $prefix, $fullBytes) !== 0) {
            return false;
        }
        $remaining = $bits % 8;
        if ($remaining === 0) {
            return true;
        }
        $mask = (0xFF << (8 - $remaining)) & 0xFF;

        return (ord($address[$fullBytes]) & $mask) === (ord($prefix[$fullBytes]) & $mask);
    }
}
