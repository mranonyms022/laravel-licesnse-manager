<?php
/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║  LICENSE GUARD — STANDALONE OFFLINE VERIFICATION                ║
 * ║                                                                  ║
 * ║  Ye file Laravel se PEHLE chalti hai.                           ║
 * ║  Package delete ho, composer.json edit ho, middleware hata lo — ║
 * ║  ye file tab bhi kaam karti hai.                                ║
 * ║                                                                  ║
 * ║  Include karo bootstrap/app.php ki SABSE PEHLI LINE mein:       ║
 * ║  require_once __DIR__ . '/license-guard.php';                   ║
 * ║                                                                  ║
 * ║  File permissions: chattr +i bootstrap/license-guard.php        ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */

(static function (): void {

    // ── Constants ─────────────────────────────────────────────────────
    $CACHE_FILE   = dirname(__DIR__) . '/storage/app/.lic_cache';
    $STORAGE_FILE = dirname(__DIR__) . '/storage/app/.lic';
    $CACHE_TTL    = 300; // 5 minutes

    // ── Step 1: Read token ────────────────────────────────────────────
    // Primary: from environment (.env loaded by PHP itself before this runs
    // because .env is read in public/index.php before bootstrap/app.php)
    // Actually .env is loaded by Dotenv inside create-application — so we
    // read directly from $_ENV or getenv:
    $token     = trim(getenv('LICENSE_TOKEN') ?: ($_ENV['LICENSE_TOKEN'] ?? ''));
    $publicKey = base64_decode(getenv('LICENSE_PUBLIC_KEY') ?: ($_ENV['LICENSE_PUBLIC_KEY'] ?? ''));
    $fprKey    = base64_decode(getenv('LICENSE_FPR_KEY') ?: ($_ENV['LICENSE_FPR_KEY'] ?? ''));

    // .env not loaded yet at this point in some setups — read it manually
    if (! $token || strlen($token) < 20) {
        $envFile = dirname(__DIR__) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with($line, '#')) continue;
                if (! str_contains($line, '=')) continue;
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v, " \t\"'");
                match ($k) {
                    'LICENSE_TOKEN'      => $token     = $v,
                    'LICENSE_PUBLIC_KEY' => $publicKey = base64_decode($v),
                    'LICENSE_FPR_KEY'    => $fprKey    = base64_decode($v),
                    default              => null,
                };
            }
        }
    }

    // Fallback: storage file
    if ((! $token || strlen($token) < 20) && file_exists($STORAGE_FILE)) {
        $token = trim(file_get_contents($STORAGE_FILE));
    }

    // ── Step 2: Check cache ───────────────────────────────────────────
    if (file_exists($CACHE_FILE)) {
        $cache = @json_decode(file_get_contents($CACHE_FILE), true);
        if (
            is_array($cache)
            && ($cache['valid'] ?? false) === true
            && time() < ($cache['grace_end'] ?? 0)
            && time() < (($cache['cached_at'] ?? 0) + $CACHE_TTL)
        ) {
            return; // ✓ Cache valid — allow through
        }
    }

    // ── Step 3: Require sodium ────────────────────────────────────────
    if (! function_exists('sodium_crypto_sign_verify_detached')) {
        // Sodium not available — fail open with a warning
        // (better than breaking the app for sodium config issues)
        error_log('[LICENSE] WARNING: sodium extension not loaded. License check skipped.');
        return;
    }

    // ── Step 4: Token must exist ──────────────────────────────────────
    if (! $token || strlen($token) < 20) {
        _lic_block('NO_TOKEN');
    }

    if (! $publicKey || strlen($publicKey) < 10) {
        _lic_block('NO_PUBLIC_KEY');
    }

    // ── Step 5: Parse JWS (header.payload.signature) ──────────────────
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        _lic_block('INVALID_FORMAT');
    }

    [$hB64, $pB64, $sB64] = $parts;

    // ── Step 6: Ed25519 signature verification ────────────────────────
    $message = $hB64 . '.' . $pB64;
    $sig     = base64_decode(strtr($sB64, '-_', '+/') . str_repeat('=', (4 - strlen($sB64) % 4) % 4));

    if (strlen($sig) !== SODIUM_CRYPTO_SIGN_BYTES) {
        _lic_block('SIGNATURE_SIZE_INVALID');
    }

    if (! sodium_crypto_sign_verify_detached($sig, $message, $publicKey)) {
        _lic_block('SIGNATURE_INVALID');
    }

    // ── Step 7: Decode payload ────────────────────────────────────────
    $payloadJson = base64_decode(strtr($pB64, '-_', '+/') . str_repeat('=', (4 - strlen($pB64) % 4) % 4));
    $payload     = json_decode($payloadJson, true);

    if (! is_array($payload) || empty($payload)) {
        _lic_block('PAYLOAD_DECODE_ERROR');
    }

    // ── Step 8: Domain check ──────────────────────────────────────────
    // Read domain from HTTP_HOST (web) or APP_URL (CLI)
    if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') {
        $appUrl = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? '');
        // Read from .env if not set
        if (! $appUrl) {
            $envFile = dirname(__DIR__) . '/.env';
            if (file_exists($envFile)) {
                foreach (file($envFile, FILE_IGNORE_NEW_LINES) as $line) {
                    if (str_starts_with(trim($line), 'APP_URL=')) {
                        $appUrl = trim(explode('=', $line, 2)[1] ?? '', " \t\"'");
                        break;
                    }
                }
            }
        }
        $currentDomain = strtolower(parse_url($appUrl, PHP_URL_HOST) ?? '');
    } else {
        $currentDomain = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
    }

    $tokenDomain = $payload['dom'] ?? '';

    $domainMatch = ($tokenDomain === $currentDomain);

    // Wildcard support: *.example.com matches sub.example.com
    if (! $domainMatch && str_starts_with($tokenDomain, '*.')) {
        $base        = substr($tokenDomain, 2); // Remove '*.'
        $domainMatch = ($currentDomain === $base) || str_ends_with($currentDomain, '.' . $base);
    }

    if (! $domainMatch) {
        _lic_block('DOMAIN_MISMATCH');
    }

    // ── Step 9: Expiry + grace period ─────────────────────────────────
    $exp      = (int) ($payload['exp'] ?? 0);
    $grace    = (int) ($payload['grc'] ?? 0);
    $graceEnd = $exp + ($grace * 86400);

    if (time() > $graceEnd) {
        _lic_block('EXPIRED');
    }

    // ── Step 10: Fingerprint ──────────────────────────────────────────
    if ($fprKey && strlen($fprKey) > 5) {
        $expectedFpr = hash_hmac(
            'sha256',
            ($payload['dom'] ?? '') . '|' . ($payload['lic'] ?? '') . '|' . date('Y-m-d', $exp),
            $fprKey
        );
        if (! hash_equals($expectedFpr, (string) ($payload['fpr'] ?? ''))) {
            _lic_block('FINGERPRINT_INVALID');
        }
    }

    // ── All checks passed → Write cache ───────────────────────────────
    $cacheData = json_encode([
        'valid'      => true,
        'grace_end'  => $graceEnd,
        'cached_at'  => time(),
        'domain'     => $currentDomain,
    ]);

    @file_put_contents($CACHE_FILE, $cacheData);
    @chmod($CACHE_FILE, 0600);

    // Done — allow Laravel to boot
})();


/**
 * Render the license expired page and halt execution.
 * Never throws — always sends HTML response directly and exits.
 */
function _lic_block(string $reason): never
{
    // ── Console: log and continue (don't kill queue workers) ─────────
    if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') {
        // Only block on explicit artisan commands if not a queue/schedule
        $argv0 = $_SERVER['argv'][1] ?? '';
        $safeCommands = ['queue:', 'horizon', 'schedule:run', 'schedule:work'];
        foreach ($safeCommands as $safe) {
            if (str_starts_with($argv0, $safe)) {
                // Queue/schedule — log but don't halt
                fwrite(STDERR, "[LICENSE WARN] {$reason} — running in queue/schedule context.\n");
                return;
            }
        }
        fwrite(STDERR, "[LICENSE] BLOCKED: {$reason}\n");
        exit(1);
    }

    // ── Web: read display config ──────────────────────────────────────
    $appName = 'Application';
    $emails  = [];
    $phone   = '';

    $envFile = dirname(__DIR__) . '/.env';
    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $v = trim($v, " \t\"'");
            match (trim($k)) {
                'LICENSE_APP_NAME'      => $appName = $v,
                'APP_NAME'              => $appName = ($appName === 'Application' ? $v : $appName),
                'LICENSE_SUPPORT_EMAILS'=> $emails  = array_filter(explode(',', $v)),
                'LICENSE_SUPPORT_PHONE' => $phone   = $v,
                default                 => null,
            };
        }
    }

    http_response_code(402);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache');
    header('X-License-Status: ' . $reason);

    $eName  = htmlspecialchars($appName);
    $eReason = htmlspecialchars($reason);

    $contacts = '';
    foreach ($emails as $email) {
        $eEmail  = htmlspecialchars(trim($email));
        $subject = rawurlencode("License Renewal Request — {$appName}");
        $contacts .= <<<HTML
        <a href="mailto:{$eEmail}?subject={$subject}" class="contact">
            <span class="ci">✉</span>
            <span><span class="cl">Email Support</span><span class="cv">{$eEmail}</span></span>
        </a>
HTML;
    }
    if ($phone) {
        $ePhone = htmlspecialchars(trim($phone));
        $tel    = preg_replace('/[^\d+]/', '', $ePhone);
        $contacts .= <<<HTML
        <a href="tel:{$tel}" class="contact">
            <span class="ci">☎</span>
            <span><span class="cl">Phone Support</span><span class="cv">{$ePhone}</span></span>
        </a>
HTML;
    }

    if (! $contacts) {
        $contacts = '<p style="color:rgba(255,255,255,.35);font-size:.875rem;line-height:1.6">Contact your software provider to renew your license and restore access.</p>';
    }

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow,noarchive">
<title>{$eName} — License Expired</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#03060f;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 700px 500px at 50% -5%,rgba(220,38,38,.15) 0%,transparent 65%),radial-gradient(ellipse 400px 300px at 90% 110%,rgba(220,38,38,.07) 0%,transparent 60%);pointer-events:none}
body::after{content:'';position:fixed;inset:0;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:56px 56px;pointer-events:none}
.wrap{position:relative;z-index:1;text-align:center;padding:2rem;max-width:500px;width:100%}
.lock{width:70px;height:70px;border-radius:18px;background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.22);display:flex;align-items:center;justify-content:center;margin:0 auto 1.75rem;font-size:30px;animation:pulse 3s ease-in-out infinite}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(220,38,38,.25)}50%{box-shadow:0 0 0 14px rgba(220,38,38,0)}}
.badge{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .85rem;border-radius:999px;background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.18);font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#ff5555;margin-bottom:1.25rem}
.badge::before{content:'';width:5px;height:5px;border-radius:50%;background:#ff5555}
h1{font-size:1.7rem;font-weight:600;color:#fff;letter-spacing:-.02em;line-height:1.2;margin-bottom:.65rem}
.sub{color:rgba(255,255,255,.35);font-size:.925rem;margin-bottom:2rem}
.card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:1.5rem;margin-bottom:1rem;backdrop-filter:blur(8px)}
.card-label{font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.28);margin-bottom:1rem}
.contact{display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:9px;text-decoration:none;color:#e2e8f0;margin-bottom:.5rem;transition:background .2s}
.contact:hover{background:rgba(255,255,255,.07)}
.ci{width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.06);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px}
.cl{display:block;font-size:.7rem;color:rgba(255,255,255,.3)}
.cv{display:block;font-size:.875rem;font-weight:500}
.code{font-size:.7rem;color:rgba(255,255,255,.18);margin-top:2rem;font-family:monospace;letter-spacing:.04em}
</style>
</head>
<body>
<div class="wrap">
    <div class="lock">🔒</div>
    <div class="badge">License Expired</div>
    <h1>Application Unavailable</h1>
    <div class="sub">{$eName}</div>
    <div class="card">
        <div class="card-label">Contact Support to Renew</div>
        {$contacts}
    </div>
    <div class="code">HTTP 402 &nbsp;·&nbsp; {$eReason} &nbsp;·&nbsp; {$eName} &nbsp;·&nbsp; {$_SERVER['HTTP_HOST']}</div>
</div>
</body>
</html>
HTML;

    exit(0);
}
