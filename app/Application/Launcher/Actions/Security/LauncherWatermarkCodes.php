<?php

namespace App\Application\Launcher\Actions\Security;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * One random 8-byte (16 hex) watermark code per user, created on first need.
 */
final class LauncherWatermarkCodes
{
    public function forUser(int $userId): string
    {
        $existing = DB::table('launcher_watermarks')->where('user_id', $userId)->value('code');
        if ($existing !== null) {
            return $existing;
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            // Never all-zero: a zeroed field in a dump must not look like a valid code.
            $code = bin2hex(random_bytes(8));
            if ($code === str_repeat('0', 16)) {
                continue;
            }
            try {
                DB::table('launcher_watermarks')->insert([
                    'user_id' => $userId,
                    'code' => $code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return $code;
            } catch (QueryException) {
                // Either a concurrent request already created this user's code, or a
                // (astronomically unlikely) code collision -- re-read, then retry.
                $existing = DB::table('launcher_watermarks')->where('user_id', $userId)->value('code');
                if ($existing !== null) {
                    return $existing;
                }
            }
        }

        throw new \RuntimeException('Could not allocate a watermark code.');
    }

    /**
     * Finds the account for a code found in a leaked file. Accepts the hex with spaces or
     * dashes, as copied from a hex editor.
     */
    public function findUserId(string $hex): ?int
    {
        $clean = strtolower(preg_replace('/[^0-9a-fA-F]/', '', $hex) ?? '');
        if (strlen($clean) !== 16) {
            return null;
        }
        $userId = DB::table('launcher_watermarks')->where('code', $clean)->value('user_id');

        return $userId !== null ? (int)$userId : null;
    }
}
