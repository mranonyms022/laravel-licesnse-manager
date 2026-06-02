# Laravel License Package — Complete Installation Guide

## PART 1: Package Ko Client Project Mein Kaise Lagayein

### Step 1: Package ko client project ke `composer.json` mein add karo

Client ke project root mein `composer.json` kholo. Ye do cheezein add karo:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/laravel-license",
            "options": {
                "symlink": false
            }
        }
    ],
    "require": {
        "php": "^8.3",
        "laravel/framework": "^11.0",
        "yourvendor/laravel-license": "*"
    }
}
```

### Step 2: Package folder copy karo

```
client-project/
├── app/
├── bootstrap/
├── packages/
│   └── laravel-license/          ← YE FOLDER COPY KARO (zip se)
│       ├── composer.json
│       ├── config/
│       │   └── license.php
│       ├── src/
│       │   ├── LicenseServiceProvider.php
│       │   ├── Services/
│       │   ├── Http/
│       │   └── Console/
│       └── views/
│           ├── expired.blade.php
│           └── admin/
│               └── dashboard.blade.php
├── composer.json                  ← YE EDIT KARO (Step 1)
└── .env                           ← YE EDIT KARO (Step 4)
```

### Step 3: Composer install chalao

```bash
composer update yourvendor/laravel-license
```

Ya pehli baar:
```bash
composer require yourvendor/laravel-license
```

Laravel automatically ServiceProvider detect kar lega (auto-discovery via composer.json extra.laravel).

### Step 4: `.env` mein license variables add karo

```env
# Tumhare license server se milega:
LICENSE_TOKEN=eyJhbGciOiJFZERTQSIsInR5cCI6IkxJQyJ9.eyJpYXQiOjE3...
LICENSE_PUBLIC_KEY=MCowBQYDK2VdAyEA...
LICENSE_FPR_KEY=base64_fingerprint_key...

# Expired page pe dikhega:
LICENSE_APP_NAME="Client ka App Name"
LICENSE_SUPPORT_EMAILS=support@yourcompany.com,billing@yourcompany.com
LICENSE_SUPPORT_PHONE=+91-9876543210

# Optional: sirf is IP se admin panel access ho
# LICENSE_ADMIN_IPS=203.0.113.5
```

### Step 5: Config publish karo

```bash
php artisan vendor:publish --tag=license-config
```

Ye `config/license.php` create karega.

### Step 6: Middleware add karo `bootstrap/app.php` mein

```php
<?php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ─── LICENSE CHECK — Sabse pehle add karo ───────────────────────
        $middleware->prependToGroup('web', \YourVendor\LaravelLicense\Http\Middleware\LicenseCheck::class);
        // ────────────────────────────────────────────────────────────────

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### Step 7: Cron setup karo (server pe)

```bash
crontab -e
```

Ye line add karo:
```
* * * * * cd /path/to/client/project && php artisan schedule:run >> /dev/null 2>&1
```

### Step 8: Test karo

```bash
# Pehle admin token banao
php artisan license:admin-token

# Output milega:
# ⚠  One-time URL — expires in 300s:
# https://app.client.com/__lic_a3f9b2c1?_ltoken=xK9mP2...
```

URL browser mein open karo — admin panel dikhega.

---

## PART 2: Agar Developer Package Delete Kare Toh Kya Hoga

### Scenario 1: Developer `packages/laravel-license` folder delete karta hai

```bash
# Developer ne ye kiya:
rm -rf packages/laravel-license

# Result:
# composer autoload class dhundhega → class nahi milegi
# Laravel boot pe FatalError → App completely crash
# ERROR: Class "YourVendor\LaravelLicense\LicenseServiceProvider" not found
```

**App crash ho jaayegi — ye ek tarah ka tamper protection hai.**
Client khud hi call karega ki "app kaam nahi kar raha."

Tumhara jawab: "Package reinstall karo."

### Scenario 2: Developer `composer.json` se package remove karta hai aur `composer update` chalata hai

```bash
# Developer ne ye kiya:
# composer.json se "yourvendor/laravel-license" hataya
composer update

# Result:
# Package uninstall → ServiceProvider unregistered
# Middleware nahi lagega → App bina license check ke chale
```

**YE EK REAL BYPASS HAI** — isliye hum extra protection lagate hain.

### Solution: Bootstrap-level Hard Guard

Ye file directly `bootstrap/app.php` ke sath ek companion file hai jo **package ke bina bhi run hoti hai:**

---

## PART 3: Bootstrap Hard Guard (Delete-Proof Protection)

Ye `bootstrap/license-guard.php` file banao client project mein:

```php
<?php
// bootstrap/license-guard.php
// YE FILE DELETE KARNA IMPOSSIBLE BANAO — FILE PERMISSIONS SE

/**
 * Hard-coded license guard.
 * Package delete ho ya composer.json se hata diya jaye —
 * ye file directly verify karti hai aur app band kar deti hai.
 *
 * Isko bootstrap/app.php ke TOP pe include kiya jaata hai.
 */

(function () {
    // ── Config (hardcode karo, .env pe depend mat karo) ──────────────
    $publicKey   = base64_decode(getenv('LICENSE_PUBLIC_KEY') ?: '');
    $fprKey      = base64_decode(getenv('LICENSE_FPR_KEY') ?: '');
    $token       = getenv('LICENSE_TOKEN') ?: '';

    // Token fallback: storage file
    $storagePath = dirname(__DIR__) . '/storage/app/.lic';
    if ((! $token || strlen($token) < 20) && file_exists($storagePath)) {
        $token = trim(file_get_contents($storagePath));
    }

    // Cache check — avoid verifying on every single request
    $cacheFile = dirname(__DIR__) . '/storage/app/.lic_cache';
    if (file_exists($cacheFile)) {
        $cache = @json_decode(file_get_contents($cacheFile), true);
        if (
            is_array($cache)
            && isset($cache['valid'], $cache['exp'])
            && $cache['valid'] === true
            && time() < $cache['exp']
            && time() < ($cache['cached_at'] + 300) // 5 min cache
        ) {
            return; // Valid cache — allow
        }
    }

    // ── No token → block ─────────────────────────────────────────────
    if (! $token || strlen($token) < 20 || ! $publicKey) {
        self_render_blocked('NO_TOKEN');
    }

    // ── Parse token ───────────────────────────────────────────────────
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        self_render_blocked('INVALID_FORMAT');
    }

    [$headerB64, $payloadB64, $sigB64] = $parts;

    // ── Ed25519 signature verify ──────────────────────────────────────
    $message  = $headerB64 . '.' . $payloadB64;
    $sig      = base64_decode(strtr($sigB64, '-_', '+/') . str_repeat('=', (4 - strlen($sigB64) % 4) % 4));
    if (! sodium_crypto_sign_verify_detached($sig, $message, $publicKey)) {
        self_render_blocked('SIGNATURE_INVALID');
    }

    // ── Decode payload ────────────────────────────────────────────────
    $payload = json_decode(
        base64_decode(strtr($payloadB64, '-_', '+/') . str_repeat('=', (4 - strlen($payloadB64) % 4) % 4)),
        true
    );

    // ── Domain check ──────────────────────────────────────────────────
    $host = $_SERVER['HTTP_HOST'] ?? parse_url(getenv('APP_URL') ?: '', PHP_URL_HOST) ?? '';
    $host = strtolower(explode(':', $host)[0]); // Remove port if present
    if (($payload['dom'] ?? '') !== $host) {
        // Wildcard check
        $dom = $payload['dom'] ?? '';
        $isWildcard = str_starts_with($dom, '*.') && str_ends_with($host, '.' . substr($dom, 2));
        if (! $isWildcard) {
            self_render_blocked('DOMAIN_MISMATCH');
        }
    }

    // ── Expiry + grace ────────────────────────────────────────────────
    $exp      = $payload['exp'] ?? 0;
    $grace    = ($payload['grc'] ?? 0) * 86400;
    $graceEnd = $exp + $grace;
    if (time() > $graceEnd) {
        self_render_blocked('EXPIRED');
    }

    // ── Fingerprint ───────────────────────────────────────────────────
    if ($fprKey) {
        $expected = hash_hmac(
            'sha256',
            ($payload['dom'] ?? '') . '|' . ($payload['lic'] ?? '') . '|' . date('Y-m-d', $exp),
            $fprKey
        );
        if (! hash_equals($expected, $payload['fpr'] ?? '')) {
            self_render_blocked('FINGERPRINT_INVALID');
        }
    }

    // ── Write cache ───────────────────────────────────────────────────
    file_put_contents($cacheFile, json_encode([
        'valid'     => true,
        'exp'       => $graceEnd,
        'cached_at' => time(),
    ]));
    chmod($cacheFile, 0600);

})();

// ─────────────────────────────────────────────────────────────────────
// Helper — render block page and exit BEFORE Laravel boots
// ─────────────────────────────────────────────────────────────────────
function self_render_blocked(string $reason): never
{
    $appName  = getenv('LICENSE_APP_NAME') ?: getenv('APP_NAME') ?: 'Application';
    $emails   = array_filter(explode(',', getenv('LICENSE_SUPPORT_EMAILS') ?: ''));
    $phone    = getenv('LICENSE_SUPPORT_PHONE') ?: '';

    // Running in console (artisan, queue, cron)?
    if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') {
        fwrite(STDERR, "[LICENSE] Blocked: {$reason}\n");
        // Don't exit in console — let it log but not crash queues
        return;
    }

    http_response_code(402);
    header('Content-Type: text/html; charset=utf-8');
    header('X-License-Status: ' . $reason);

    $emailsHtml = '';
    foreach ($emails as $email) {
        $emailsHtml .= "<a href=\"mailto:{$email}?subject=License+Renewal+Request\" class=\"contact-item\">
            <div class=\"contact-icon\">✉</div>
            <div><div class=\"contact-label\">Email Support</div><div class=\"contact-value\">{$email}</div></div>
        </a>";
    }
    if ($phone) {
        $emailsHtml .= "<a href=\"tel:{$phone}\" class=\"contact-item\">
            <div class=\"contact-icon\">☎</div>
            <div><div class=\"contact-label\">Phone Support</div><div class=\"contact-value\">{$phone}</div></div>
        </a>";
    }

    echo <<<HTML
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>{$appName} — License Expired</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,sans-serif;background:#03060f;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 600px 400px at 50% -10%,rgba(220,38,38,.18) 0%,transparent 70%);pointer-events:none}
.wrap{text-align:center;padding:2rem;max-width:480px;position:relative;z-index:1}
.icon{width:64px;height:64px;border-radius:16px;background:rgba(220,38,38,.12);border:1px solid rgba(220,38,38,.25);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:28px}
.badge{display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .85rem;border-radius:999px;background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.2);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#ff4d4d;margin-bottom:1.25rem}
h1{font-size:1.6rem;font-weight:600;color:#fff;margin-bottom:.6rem}
.sub{color:rgba(255,255,255,.4);font-size:.9rem;margin-bottom:2rem}
.card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:1.5rem;margin-bottom:1rem}
.label{font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:1rem}
.contact-item{display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;text-decoration:none;color:#e2e8f0;margin-bottom:.5rem}
.contact-icon{width:32px;height:32px;border-radius:7px;background:rgba(255,255,255,.06);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px}
.contact-label{font-size:.7rem;color:rgba(255,255,255,.3)}
.contact-value{font-size:.875rem;font-weight:500}
.code{font-size:.72rem;color:rgba(255,255,255,.2);margin-top:1.5rem;font-family:monospace}
</style></head><body>
<div class="wrap">
<div class="icon">🔒</div>
<div class="badge">License Expired</div>
<h1>Application Unavailable</h1>
<div class="sub">{$appName}</div>
HTML;

    if ($emailsHtml) {
        echo "<div class=\"card\"><div class=\"label\">Contact Support to Renew</div>{$emailsHtml}</div>";
    } else {
        echo "<div class=\"card\"><div class=\"label\">License Renewal Required</div><p style=\"color:rgba(255,255,255,.4);font-size:.875rem;\">Please contact your software provider to renew your license.</p></div>";
    }

    echo "<div class=\"code\">HTTP 402 &middot; {$reason} &middot; " . date('Y') . "</div>";
    echo "</div></body></html>";
    exit(0);
}
```

### `bootstrap/app.php` mein include karo — SABSE PEHLI LINE:

```php
<?php
// bootstrap/app.php

// ─── LICENSE GUARD — MUST BE FIRST ────────────────────────────────────
require_once __DIR__ . '/license-guard.php';
// ──────────────────────────────────────────────────────────────────────

use Illuminate\Foundation\Application;
// ... rest of file
```

---

## PART 4: Attack Protection Table

| Attack | Package Check | Bootstrap Guard |
|--------|:---:|:---:|
| Token delete karo | ✅ Block | ✅ Block |
| Token tamper karo (expiry change) | ✅ Block (signature) | ✅ Block (signature) |
| Package folder delete karo | ❌ App crash (partial) | ✅ Block cleanly |
| `composer.json` se package hata ke update karo | ❌ Bypass | ✅ Block |
| `bootstrap/app.php` se middleware hatao | ❌ Bypass | ✅ Block |
| `license-guard.php` delete karo | N/A | App crash (require_once fails) |
| Public key replace karo | ✅ Block | ✅ Block |
| Domain change karo | ✅ Block | ✅ Block |
| `.env` se token hatao | ✅ Block | ✅ Block |

### `license-guard.php` ki permissions lock karo:

```bash
# File owner tumhara deployment user ho, www-data sirf read kare
chown deploy:www-data bootstrap/license-guard.php
chmod 644 bootstrap/license-guard.php

# Immutable banao (root access chahiye — VPS pe possible)
chattr +i bootstrap/license-guard.php
# Ab koi bhi delete nahi kar sakta, root ke bina bhi nahi
```

---

## PART 5: Complete File Permission Setup

```bash
# Client server pe ek baar chalao:

# License files — sirf web server read kare
chmod 600 storage/app/.lic        # Token file
chmod 600 storage/app/.lic_cache  # Cache file
chmod 600 .env                    # Environment

# Guard file — immutable (optional, needs root)
chattr +i bootstrap/license-guard.php

# Verify:
lsattr bootstrap/license-guard.php
# ----i--------e-- bootstrap/license-guard.php
```

---

## PART 6: Renewal Process (Complete Flow)

```
1. Tumhara License Server Admin Panel kholо
   → License dhundho
   → "Renew" click karo
   → Nayi expiry date daalo
   → "Renew & Generate Token" click karo

2. Naya token copy karo (sirf ek baar dikhta hai)

3. Client server pe jaao:
   php artisan license:admin-token
   → URL milega → browser mein kholo
   → "Update License Token" section mein token paste karo
   → Save karo

4. Done — koi deployment nahi, koi code change nahi
```
