<?php

namespace App\Application\Raffle\Support;

use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class DrawRunSeedManager
{
    public function generate(): string
    {
        return random_bytes(32);
    }

    public function commitment(string $seed): string
    {
        return hash('sha256', $seed);
    }

    public function encrypt(string $seed): string
    {
        return Crypt::encryptString(base64_encode($seed));
    }

    public function decrypt(string $ciphertext): string
    {
        $decoded = base64_decode(Crypt::decryptString($ciphertext), true);

        if ($decoded === false || strlen($decoded) !== 32) {
            throw new RuntimeException('Stored draw seed is invalid.');
        }

        return $decoded;
    }

    public function reveal(string $seed): string
    {
        return base64_encode($seed);
    }

    public function fromReveal(string $reveal): string
    {
        $seed = base64_decode($reveal, true);

        if ($seed === false || strlen($seed) !== 32) {
            throw new RuntimeException('Draw seed reveal is invalid.');
        }

        return $seed;
    }
}
