<?php

namespace YourVendor\LaravelLicense\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * LicenseAdminGuard
 *
 * Protects the hidden admin route with:
 * 1. One-time HMAC token (generated via php artisan license:admin-token)
 * 2. Session-based authentication after first token use
 * 3. 30-minute idle timeout
 * 4. Optional IP allowlist
 * 5. Returns 404 (not 403) to avoid revealing route existence
 */
class LicenseAdminGuard
{
    private const TOKEN_PREFIX  = 'lic_admin_tok:';
    private const SESSION_KEY   = 'lic_admin_authed';
    private const AUTHED_AT_KEY = 'lic_admin_authed_at';
    private const SESSION_TTL   = 1800; // 30 minutes

    public function handle(Request $request, Closure $next): mixed
    {
        // ── IP allowlist check (if configured) ────────────────────────────
        $allowedIps = array_filter(
            explode(',', config('license.admin_ips', ''))
        );

        if (! empty($allowedIps) && ! in_array($request->ip(), $allowedIps, true)) {
            abort(404);
        }

        // ── Session already authenticated? ────────────────────────────────
        if ($request->session()->get(self::SESSION_KEY) === true) {
            // Check idle timeout
            $authedAt = $request->session()->get(self::AUTHED_AT_KEY, 0);
            if ((time() - $authedAt) > self::SESSION_TTL) {
                $request->session()->forget([self::SESSION_KEY, self::AUTHED_AT_KEY]);
                abort(404); // Session expired
            }

            // Refresh idle timer
            $request->session()->put(self::AUTHED_AT_KEY, time());
            return $next($request);
        }

        // ── One-time token check ──────────────────────────────────────────
        $token = $request->query('_ltoken');
        if (! $token) {
            abort(404);
        }

        $cacheKey = self::TOKEN_PREFIX . hash('sha256', $token);

        // Token must exist in cache
        if (! Cache::has($cacheKey)) {
            abort(404); // Expired or already used
        }

        // Consume the token — one-time use only
        Cache::forget($cacheKey);

        // Authenticate the session
        $request->session()->put(self::SESSION_KEY, true);
        $request->session()->put(self::AUTHED_AT_KEY, time());
        $request->session()->regenerate(); // Prevent session fixation

        // Redirect without token in URL (no token leakage in server logs)
        return redirect()->route('license.admin.dashboard');
    }
}
