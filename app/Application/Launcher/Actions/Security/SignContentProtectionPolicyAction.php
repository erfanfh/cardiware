<?php

namespace App\Application\Launcher\Actions\Security;

use RuntimeException;

/**
 * Signs a sale item's content_protection policy for the launcher (1.21.118+).
 *
 * The policy decides which Lua modules sider.dll trusts with decrypted content, whether
 * strict module loading is on and which paths are protected. The content KEY has always
 * travelled inside the RS256-signed offline-licence payload, but the policy was plain
 * manifest JSON protected by TLS only: a customer running a MITM proxy with their own CA on
 * their own PC could add a dump module to the allowlist at "content" tier. The launcher now
 * verifies this signature -- made with the SAME key that signs the offline licence, whose key
 * id it pins -- before handing the policy to sider.
 *
 * Contract (see docs/SIGNED-CONTENT-POLICY-FA.md in the launcher repository):
 *   content_protection_json      the policy + "content_key_id", as ONE exact JSON string
 *   content_protection_signature base64url(RSASSA-PKCS1-v1_5 / SHA-256 over that string's UTF-8 bytes)
 *   content_protection_key_id    sha256 of the public key PEM text == the offline licence key_id
 *
 * The string that is signed is the string that is sent, byte for byte; never re-encode it.
 */
final class SignContentProtectionPolicyAction
{
    public function __construct(private readonly IssueOfflineLicenseAction $licenses)
    {
    }

    /**
     * @param array<string, mixed> $policy the content_protection block exactly as the manifest sends it
     *
     * @return array{content_protection_json: string, content_protection_signature: string, content_protection_key_id: string}
     */
    public function execute(array $policy, string $contentKeyId): array
    {
        $policy['content_key_id'] = $contentKeyId;

        $json = json_encode(
            $policy,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        [$privateKey, $publicKey] = $this->licenses->loadOrCreateSigningKey();

        if (!openssl_sign($json, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('امضای سیاست محافظت محتوا انجام نشد.');
        }

        return [
            'content_protection_json' => $json,
            'content_protection_signature' => rtrim(strtr(base64_encode($signature), '+/', '-_'), '='),
            'content_protection_key_id' => hash('sha256', $publicKey),
        ];
    }
}
