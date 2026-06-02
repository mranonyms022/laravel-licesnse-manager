<?php

namespace YourVendor\LaravelLicense\Services;

/**
 * TokenVerifier — Pure offline Ed25519 license verification.
 *
 * No HTTP calls. No database. No cache dependency.
 * If the token signature is valid → license data is trustworthy.
 * The private key never leaves the license server, so tokens cannot be forged.
 */
class TokenVerifier
{
    /**
     * Verify the license token completely offline.
     *
     * Verification steps:
     *  1. Token exists
     *  2. Token format is valid (3-part JWS)
     *  3. Ed25519 signature is valid (tamper detection)
     *  4. Domain matches current request
     *  5. Expiry + grace period check
     *  6. Fingerprint claim verification (proves token came from YOUR server)
     */
    public function verify(): VerificationResult
    {
        // ── Step 1: Read token ─────────────────────────────────────────────
        $token = $this->readToken();
        if (! $token) {
            return VerificationResult::fail('NO_TOKEN');
        }

        // ── Step 2: Parse structure ───────────────────────────────────────
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return VerificationResult::fail('INVALID_FORMAT');
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;

        // ── Step 3: Verify Ed25519 signature ──────────────────────────────
        // If ANYONE modifies the token (payload, expiry, domain) → this fails
        $publicKeyB64 = config('license.public_key');
        if (! $publicKeyB64) {
            return VerificationResult::fail('NO_PUBLIC_KEY');
        }

        $publicKey = base64_decode($publicKeyB64);
        $message   = $headerB64 . '.' . $payloadB64;

        // Normalize base64url to standard base64
        $sigRaw = base64_decode(strtr($sigB64, '-_', '+/') . str_repeat('=', (4 - strlen($sigB64) % 4) % 4));

        if (! sodium_crypto_sign_verify_detached($sigRaw, $message, $publicKey)) {
            return VerificationResult::fail('SIGNATURE_INVALID');
        }

        // ── Step 4: Decode payload ────────────────────────────────────────
        $payloadJson = base64_decode(strtr($payloadB64, '-_', '+/') . str_repeat('=', (4 - strlen($payloadB64) % 4) % 4));
        $payload     = json_decode($payloadJson, true);

        if (! is_array($payload)) {
            return VerificationResult::fail('PAYLOAD_DECODE_ERROR');
        }

        // ── Step 5: Domain check ──────────────────────────────────────────
        $currentDomain = $this->getCurrentDomain();
        $tokenDomain   = $payload['dom'] ?? '';

        if (! $this->domainsMatch($tokenDomain, $currentDomain)) {
            return VerificationResult::fail('DOMAIN_MISMATCH', [
                'expected' => $tokenDomain,
                'got'      => $currentDomain,
            ]);
        }

        // ── Step 6: Expiry + grace period ────────────────────────────────
        $now      = time();
        $exp      = $payload['exp'] ?? 0;
        $grace    = (int) ($payload['grc'] ?? 0);
        $graceEnd = $exp + ($grace * 86400);

        if ($now > $graceEnd) {
            return VerificationResult::fail('EXPIRED', [
                'expired_at'  => date('Y-m-d H:i:s', $exp),
                'grace_ended' => date('Y-m-d H:i:s', $graceEnd),
            ]);
        }

        // ── Step 7: Fingerprint verification ─────────────────────────────
        // Proves token was issued by YOUR server (not a forged token)
        $fprKey = config('license.fpr_key');
        if ($fprKey) {
            $expectedFpr = hash_hmac(
                'sha256',
                ($payload['dom'] ?? '') . '|' .
                ($payload['lic'] ?? '') . '|' .
                date('Y-m-d', $exp),
                base64_decode($fprKey)
            );

            if (! hash_equals($expectedFpr, $payload['fpr'] ?? '')) {
                return VerificationResult::fail('FINGERPRINT_INVALID');
            }
        }

        // ── All checks passed ─────────────────────────────────────────────
        if ($now > $exp) {
            return VerificationResult::grace($payload); // In grace period
        }

        return VerificationResult::valid($payload);
    }

    /**
     * Read token from config (which reads from .env).
     * Also checks a fallback file in case .env was cleared.
     */
    private function readToken(): ?string
    {
        // Primary: from .env via config
        $token = config('license.token');
        if ($token && strlen($token) > 20) {
            return trim($token);
        }

        // Fallback: from storage file (if admin saved it there)
        $path = storage_path('app/.lic');
        if (file_exists($path)) {
            $content = trim(file_get_contents($path));
            if ($content && strlen($content) > 20) {
                return $content;
            }
        }

        return null;
    }

    private function getCurrentDomain(): string
    {
        // CLI/console — use APP_URL
        if (app()->runningInConsole()) {
            return parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        }

        return request()->getHost();
    }

    private function domainsMatch(string $expected, string $current): bool
    {
        // Exact match
        if ($expected === $current) return true;

        // Wildcard: token domain starts with '*.'
        if (str_starts_with($expected, '*.')) {
            $pattern = substr($expected, 2); // Remove '*.'
            // current must end with .pattern or equal pattern
            return str_ends_with($current, '.' . $pattern) || $current === $pattern;
        }

        return false;
    }
}
