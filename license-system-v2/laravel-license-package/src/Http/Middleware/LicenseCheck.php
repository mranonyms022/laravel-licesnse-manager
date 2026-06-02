<?php

namespace YourVendor\LaravelLicense\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use YourVendor\LaravelLicense\Services\TokenVerifier;
use YourVendor\LaravelLicense\Services\VerificationResult;

class LicenseCheck
{
    public function __construct(private TokenVerifier $verifier) {}

    public function handle(Request $request, Closure $next): mixed
    {
        // Skip verification for the license expired page itself
        // (to avoid infinite loop)
        if ($request->routeIs('license.*')) {
            return $next($request);
        }

        $result = $this->getVerificationResult();

        if (! $result->valid) {
            return $this->blocked($result);
        }

        // Optional: warn in logs if in grace period
        if ($result->inGracePeriod) {
            logger()->warning('[License] In grace period', [
                'reason'  => $result->reason,
                'domain'  => $result->domain(),
                'expires' => $result->expiresAt()?->format('Y-m-d'),
            ]);
        }

        return $next($request);
    }

    private function getVerificationResult(): VerificationResult
    {
        $cacheKey = 'lic_verify_result';
        $ttl      = config('license.cache_ttl', 300);

        // Check cache first
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            // Reconstruct from cached array
            return new VerificationResult(
                valid:         $cached['valid'],
                inGracePeriod: $cached['in_grace'],
                reason:        $cached['reason'],
                payload:       $cached['payload'] ?? [],
            );
        }

        // Fresh verification
        $result = $this->verifier->verify();

        // Cache the result (both valid and invalid)
        // Invalid results cached for shorter time so genuine fixes take effect faster
        $cacheDuration = $result->valid ? $ttl : min($ttl, 60);

        Cache::put($cacheKey, [
            'valid'    => $result->valid,
            'in_grace' => $result->inGracePeriod,
            'reason'   => $result->reason,
            'payload'  => $result->payload,
        ], $cacheDuration);

        return $result;
    }

    private function blocked(VerificationResult $result): \Illuminate\Http\Response
    {
        // Console commands — don't block, just warn
        if (app()->runningInConsole()) {
            logger()->critical('[License] Verification failed in console', ['reason' => $result->reason]);
            return response('', 200); // Won't be used in console context
        }

        // Web requests — show expired page
        return response(
            view('license::expired', [
                'reason'    => $result->reason,
                'app_name'  => config('license.app_name', config('app.name')),
                'emails'    => config('license.support_emails', []),
                'phone'     => config('license.support_phone', ''),
            ]),
            402  // Payment Required — distinguishes license issues from 500 errors
        );
    }
}
