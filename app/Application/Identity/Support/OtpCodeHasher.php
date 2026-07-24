<?php

namespace App\Application\Identity\Support;

use RuntimeException;

final class OtpCodeHasher
{
    public function make(string|int $code): string
    {
        return hash_hmac(
            'sha256',
            (string)$code,
            $this->key()
        );
    }

    public function check(
        string|int $code,
        ?string    $codeHash,
    ): bool
    {
        if (blank($codeHash)) {
            return false;
        }

        return hash_equals(
            $codeHash,
            $this->make($code)
        );
    }

    private function key(): string
    {
        $key = (string)config('app.key');

        if (blank($key)) {
            throw new RuntimeException(
                'Application encryption key is not configured.'
            );
        }

        if (str_starts_with($key, 'base64:')) {
            $decodedKey = base64_decode(
                substr($key, 7),
                true
            );

            if ($decodedKey === false) {
                throw new RuntimeException(
                    'Application encryption key is invalid.'
                );
            }

            return $decodedKey;
        }

        return $key;
    }
}
