<?php

namespace App\Services;

use App\Models\License;

class TokenGeneratorService
{
    /**
     * Generate an Ed25519-signed JWT token for a license.
     *
     * This is the ONLY place tokens are created.
     * Private key never leaves this server.
     */
    public function generate(License $license): string
    {
        $privateKeyB64 = config('license.private_key');
        if (! $privateKeyB64) {
            throw new \RuntimeException('LICENSE_PRIVATE_KEY not configured on server.');
        }

        $privateKey = base64_decode($privateKeyB64);

        $payload = [
            'iat'  => time(),
            'nbf'  => time(),
            'exp'  => $license->expires_at->timestamp,

            // License data embedded in token
            'lic'  => $license->license_key,
            'dom'  => $license->domain,
            'grc'  => $license->grace_period_days,
            'prd'  => $license->product_name,
            'cli'  => $license->client_name,
            'ftr'  => $license->features ?? [],

            // Secret fingerprint — can ONLY be generated with server's fingerprint secret
            // Client can verify it, but CANNOT reproduce it
            'fpr'  => $this->generateFingerprint(
                $license->domain,
                $license->license_key,
                $license->expires_at->format('Y-m-d')
            ),

            // Unique token ID (for revocation tracking)
            'jti'  => bin2hex(random_bytes(16)),
        ];

        return $this->buildSignedToken($payload, $privateKey);
    }

    private function generateFingerprint(string $domain, string $key, string $expires): string
    {
        $secret = config('license.fingerprint_secret');
        if (! $secret) {
            throw new \RuntimeException('LICENSE_FINGERPRINT_SECRET not configured.');
        }

        return hash_hmac('sha256', $domain . '|' . $key . '|' . $expires, $secret);
    }

    private function buildSignedToken(array $payload, string $privateKey): string
    {
        $header  = $this->base64urlEncode(json_encode(['alg' => 'EdDSA', 'typ' => 'LIC']));
        $body    = $this->base64urlEncode(json_encode($payload));
        $message = $header . '.' . $body;

        $signature = sodium_crypto_sign_detached($message, $privateKey);

        return $message . '.' . $this->base64urlEncode($signature);
    }

    private function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
