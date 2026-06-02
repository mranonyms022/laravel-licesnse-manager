<?php

namespace App\Services;

use App\Models\License;
use App\Models\LicenseKeypair;

class TokenGeneratorService
{
    public function generate(License $license): string
    {
        // ── Active keypair DB se lo ───────────────────────────────────
        $keypair = LicenseKeypair::active();

        if (! $keypair) {
            throw new \RuntimeException(
                'No active keypair found. Run: php artisan license:keygen'
            );
        }

        // ── Decrypt karo ──────────────────────────────────────────────
        $privateKey = $keypair->getPrivateKeyDecrypted();
        $fprSecret  = $keypair->getFingerprintSecretDecrypted();

        // ── Payload ───────────────────────────────────────────────────
        $payload = [
            'iat' => time(),
            'exp' => $license->expires_at->timestamp,
            'lic' => $license->license_key,
            'dom' => $license->domain,
            'grc' => $license->grace_period_days,
            'prd' => $license->product_name,
            'cli' => $license->client_name,
            'ftr' => $license->features ?? [],
            'fpr' => $this->fingerprint($license, $fprSecret),
            'jti' => bin2hex(random_bytes(16)),
            'kvr' => $keypair->version,  // ← ab available hai
        ];

        return $this->sign($payload, base64_decode($privateKey));
    }

    private function fingerprint(License $license, string $secret): string
    {
        return hash_hmac(
            'sha256',
            $license->domain . '|' . $license->license_key . '|' . $license->expires_at->format('Y-m-d'),
            $secret
        );
    }

    private function sign(array $payload, string $privateKey): string
    {
        $header  = $this->b64u(json_encode(['alg' => 'EdDSA', 'typ' => 'LIC']));
        $body    = $this->b64u(json_encode($payload));
        $message = $header . '.' . $body;
        $sig     = sodium_crypto_sign_detached($message, $privateKey);

        return $message . '.' . $this->b64u($sig);
    }

    private function b64u(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
