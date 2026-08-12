<?php

namespace App\Application\Launcher\Actions\Security;

use App\Application\Launcher\Exceptions\InvalidDeviceProofException;
use Illuminate\Support\Facades\Cache;

final class VerifyDeviceProofAction
{
    /** @param array<string, mixed> $device */
    public function execute(array $device): void
    {
        $timestamp = (int)$device['proof_timestamp'];
        $allowedSkew = (int)config('launcher_security.device_proof_clock_skew_seconds', 300);

        if (abs(now()->timestamp - $timestamp) > $allowedSkew) {
            throw new InvalidDeviceProofException('ساعت سیستم دستگاه صحیح نیست یا درخواست منقضی شده است.');
        }

        $publicKeyDer = base64_decode((string)$device['public_key'], true);
        $signature = base64_decode((string)$device['proof_signature'], true);
        $nonce = (string)$device['proof_nonce'];

        if ($publicKeyDer === false || $signature === false || strlen($publicKeyDer) < 64) {
            throw new InvalidDeviceProofException();
        }

        $calculatedFingerprint = hash('sha256', $publicKeyDer);
        if (!hash_equals($calculatedFingerprint, (string)$device['public_key_fingerprint'])) {
            throw new InvalidDeviceProofException('اثر انگشت کلید عمومی دستگاه معتبر نیست.');
        }

        $nonceKey = 'launcher-device-proof:' . hash('sha256', $nonce);
        if (!Cache::add($nonceKey, true, now()->addMinutes(10))) {
            throw new InvalidDeviceProofException('این درخواست امنیتی قبلاً استفاده شده است.');
        }

        $pem = "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($publicKeyDer), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
        $key = openssl_pkey_get_public($pem);
        if ($key === false) {
            throw new InvalidDeviceProofException('کلید عمومی دستگاه قابل خواندن نیست.');
        }

        $canonical = implode("\n", [
            'MWLauncher.DeviceProof.v1',
            (string)$device['id'],
            (string)$device['fingerprint_hash'],
            (string)$device['public_key_fingerprint'],
            (string)$timestamp,
            $nonce,
        ]);

        if (openssl_verify($canonical, $signature, $key, OPENSSL_ALGO_SHA256) !== 1) {
            throw new InvalidDeviceProofException();
        }
    }
}
