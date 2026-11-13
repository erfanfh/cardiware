<?php

namespace Tests\Support;

use Illuminate\Support\Facades\File;

trait UsesEphemeralLauncherSigningKeys
{
    protected string $signingPublicKey;

    protected function useEphemeralLauncherSigningKeys(): void
    {
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        openssl_pkey_export($key, $privateKey);
        $this->signingPublicKey = openssl_pkey_get_details($key)['key'];
        $directory = storage_path('app/private/launcher');
        $files = File::partialMock();
        foreach (['private' => $privateKey, 'public' => $this->signingPublicKey] as $type => $pem) {
            $path = $directory . DIRECTORY_SEPARATOR . 'offline-license-' . $type . '.pem';
            $files->shouldReceive('isFile')->with($path)->andReturn(true);
            $files->shouldReceive('get')->with($path)->andReturn($pem);
        }
    }

    protected function verifyPolicySignature(array $signed): int
    {
        return openssl_verify(
            $signed['content_protection_json'],
            base64_decode(strtr($signed['content_protection_signature'], '-_', '+/'), true),
            $this->signingPublicKey,
            OPENSSL_ALGO_SHA256,
        );
    }
}
