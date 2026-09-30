<?php

namespace App\Application\Launcher\Actions\Security;

use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use Illuminate\Support\Facades\DB;

final class RecordLauncherSecurityEventsAction
{
    /**
     * The only event types accepted, with the severity the SERVER assigns (3 = high).
     * Anything else is rejected by validation, so a modified launcher cannot invent types
     * or downgrade what it reports.
     */
    public const TYPES = [
        // sider.dll whose SHA-256 is not a build we shipped -- the classic key-stealing move.
        'sider_dll_untrusted' => 3,
        // A debugger attached to the launcher process itself.
        'launcher_debugger' => 3,
        // Content-key pipe answered by a process other than the game we started.
        'content_key_pid_mismatch' => 3,
        // A Sider module we registered, but with different bytes -- someone edited our code.
        'module_hash_mismatch' => 2,
        // A native DLL in Sider's modules folder that is not in the panel list.
        'module_dll_unlisted' => 2,
        // A Lua module not in the panel list (often the user's own mod; low on its own).
        'module_lua_unlisted' => 1,
        // A DLL named like a Windows search-order hijack target next to the game exe.
        // Mostly legitimate (patch loaders, ReShade); what matters is a RARE hash, which
        // the admin page surfaces by counting how many accounts share each hash.
        'game_dll_hijack_name' => 1,
        // Any other DLL/ASI next to the game (hook/ASI loaders pick these up) that is not
        // on the admin allowlist.
        'game_dll_unlisted' => 1,
    ];

    /**
     * @param array<int, array{type:string, subject?:?string, sha256?:?string, count?:?int}> $events
     */
    public function execute(
        User $user,
        ?LauncherDevice $device,
        array $events,
        ?string $appVersion,
        ?string $ipAddress,
    ): int {
        $now = now();
        $recorded = 0;

        DB::transaction(function () use ($user, $device, $events, $appVersion, $ipAddress, $now, &$recorded): void {
            foreach ($events as $event) {
                $type = (string)$event['type'];
                $subject = $this->cleanSubject($event['subject'] ?? null);
                $sha256 = isset($event['sha256']) && $event['sha256'] !== null
                    ? strtolower((string)$event['sha256'])
                    : null;
                $count = max(1, min(10000, (int)($event['count'] ?? 1)));

                // One row per (device, type, subject, hash). Device is part of it so the
                // same finding on two machines of one account stays two rows -- that
                // difference is itself useful when looking at a shared account.
                $fingerprint = hash('sha256', implode("\x1f", [
                    (string)($device?->getKey() ?? '0'), $type, (string)$subject, (string)$sha256,
                ]));

                $inserted = DB::table('launcher_security_events')->insertOrIgnore([
                    'user_id' => $user->getKey(),
                    'launcher_device_id' => $device?->getKey(),
                    'event_type' => $type,
                    'severity' => self::TYPES[$type],
                    'subject' => $subject,
                    'sha256' => $sha256,
                    'app_version' => $appVersion,
                    'fingerprint' => $fingerprint,
                    'occurrences' => $count,
                    'first_seen_at' => $now,
                    'last_seen_at' => $now,
                    'last_ip' => $ipAddress,
                ]);

                if ($inserted === 0) {
                    DB::table('launcher_security_events')
                        ->where('user_id', $user->getKey())
                        ->where('fingerprint', $fingerprint)
                        ->update([
                            'occurrences' => DB::raw('LEAST(occurrences + ' . $count . ', 4000000000)'),
                            'last_seen_at' => $now,
                            'last_ip' => $ipAddress,
                            'app_version' => $appVersion,
                        ]);
                }
                $recorded++;
            }

            // Stamped even for an empty report: "this device reports" is what lets the
            // admin page list devices that are active but silent.
            if ($device !== null) {
                DB::table('launcher_devices')
                    ->where('id', $device->getKey())
                    ->update(['last_security_report_at' => $now]);
            }
        });

        return $recorded;
    }

    // Display-only text: drop control characters and keep only the last path segment so
    // no customer directory structure (user names in paths) is ever stored.
    private function cleanSubject(mixed $subject): ?string
    {
        if ($subject === null) {
            return null;
        }
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', (string)$subject) ?? '';
        $value = trim(str_replace('/', '\\', $value));
        if ($value === '') {
            return null;
        }
        // Module paths keep their relative folder (lib\CommonLib.lua); anything that looks
        // absolute (C:\..., \\server\...) is reduced to its file name.
        if (preg_match('/^[A-Za-z]:\\\\|^\\\\\\\\/', $value) === 1) {
            $value = basename(str_replace('\\', '/', $value));
        }

        return mb_substr($value, 0, 255);
    }
}
