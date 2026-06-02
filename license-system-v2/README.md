# Laravel License System — Complete Setup Guide

## Architecture Overview

```
YOUR SERVER (License Issuer)          CLIENT SERVERS
─────────────────────────────         ─────────────────────────────
php artisan license:keygen            composer require yourvendor/laravel-license
   → Ed25519 keypair                  
                                      .env:
php artisan license:issue             LICENSE_TOKEN=eyJhbGci...   ← signed by your private key
   --domain=client.com                LICENSE_PUBLIC_KEY=base64... ← verifies the token
   --expires=2026-12-31               LICENSE_FPR_KEY=base64...   ← fingerprint key
   → Signed JWT token                 
        │                             LicenseCheck middleware runs on every request
        │ (email/secure channel)      Verifies offline — no HTTP calls, no database
        ▼
   Client pastes in .env
```

---

## Part 1: License Server Setup

### 1.1 Install the license server (standard Laravel 11 project)

```bash
composer create-project laravel/laravel license-server
cd license-server

# Copy files from license-server/ folder into this project
# Then run:

php artisan migrate
php artisan license:keygen    # Generate Ed25519 keypair
```

### 1.2 .env for license server

```env
# From php artisan license:keygen output:
LICENSE_PRIVATE_KEY=<base64_private_key>     # NEVER share
LICENSE_PUBLIC_KEY=<base64_public_key>       # Safe to share with clients
LICENSE_FINGERPRINT_SECRET=<hex_secret>      # NEVER share
```

### 1.3 Issue a token for a client

```bash
php artisan license:issue \
  --domain=app.client.com \
  --key=AAAA-BBBB-CCCC-DDDD \
  --expires=2026-12-31 \
  --grace=3
```

Or use the web admin panel and click **Generate Token**.

---

## Part 2: Client Project Integration

### 2.1 Install the package

Since this is a private package, add to client's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/path/to/laravel-license-package"
        }
    ],
    "require": {
        "yourvendor/laravel-license": "*"
    }
}
```

Or host on your private Packagist/Satis server and require normally.

```bash
composer require yourvendor/laravel-license
php artisan vendor:publish --tag=license-config
```

### 2.2 .env for client project

```env
# Provided by the license server after issuance:
LICENSE_TOKEN=eyJhbGciOiJFZERTQSIsInR5cCI6IkxJQyJ9...
LICENSE_PUBLIC_KEY=<base64_public_key>
LICENSE_FPR_KEY=<base64_fingerprint_key>

# Shown on the expired page:
LICENSE_APP_NAME="My Application"
LICENSE_SUPPORT_EMAILS=support@yourcompany.com,billing@yourcompany.com
LICENSE_SUPPORT_PHONE=+91-9999999999

# Optional: restrict admin panel to your IP only
LICENSE_ADMIN_IPS=203.0.113.5
```

### 2.3 Add middleware to bootstrap/app.php

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->prependToGroup('web', \YourVendor\LaravelLicense\Http\Middleware\LicenseCheck::class);
})
```

### 2.4 Add scheduler to bootstrap/app.php (Laravel 11 style)

```php
// bootstrap/app.php
->withSchedule(function (Schedule $schedule) {
    // This is optional since ServiceProvider self-registers it
    // But adding explicitly makes it more obvious in your codebase
})
```

Make sure cron is running:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Part 3: Accessing the Hidden Admin Panel

### 3.1 Generate a one-time access URL

```bash
# SSH into the client server, then:
php artisan license:admin-token

# Output:
# ⚠  One-time URL — expires in 300s:
# https://app.client.com/__lic_a3f9b2c1?_ltoken=xK9mP2...Qr7
```

### 3.2 What you can do in the admin panel

- View current token status (valid / grace period / expired)
- See expiry date, domain binding, features
- Paste a new token when renewing
- Clear cache to force re-verification
- View raw status JSON

---

## Part 4: Token Verification Flow

```
Request arrives
    │
    ▼
LicenseCheck middleware
    │
    ├─ Cache hit (< 5 min)? ──────────────────────────→ Use cached result
    │
    └─ Cache miss → TokenVerifier::verify()
            │
            ├─ 1. Token exists? (from .env or storage/app/.lic)
            ├─ 2. Token format valid (3-part JWS)?
            ├─ 3. Ed25519 signature valid?
            │      (Any field changed → FAILS instantly)
            ├─ 4. Domain matches current request?
            ├─ 5. Not expired (including grace period)?
            └─ 6. Fingerprint claim valid?
                   (Proves token came from YOUR server)
                        │
                        ├── All pass → ✓ Allow request
                        ├── Expired but in grace → ✓ Allow + log warning
                        └── Any fail → 402 → Show expired.blade.php
```

---

## Part 5: Security Reference

### What happens when someone tries to bypass?

| Attack | Result |
|--------|--------|
| Delete `storage/app/.lic` | `.env` token is used instead |
| Remove `LICENSE_TOKEN` from `.env` | `NO_TOKEN` → blocked |
| Edit expiry date in token | Ed25519 signature fails → `SIGNATURE_INVALID` → blocked |
| Create fake token | No private key → can't produce valid signature → blocked |
| Change domain in token | `dom` claim mismatch after signature fails → blocked |
| Replace `LICENSE_PUBLIC_KEY` | Token verify fails against wrong key → blocked |
| Delete middleware from `bootstrap/app.php` | App runs unprotected — this is the one bypass that works. Protect `bootstrap/app.php` with file system permissions. |
| Delete `LicenseServiceProvider` from `composer.json` | Package not loaded — use file integrity monitoring |
| Comment out schedule in `Kernel.php` | Schedule is in `ServiceProvider`, not `Kernel.php` — still runs |

### Recommended file permissions

```bash
chmod 600 .env
chmod 600 storage/app/.lic
chown www-data:www-data storage/app/.lic
```

---

## Part 6: Renewal Process

1. Log in to your License Server admin panel
2. Find the client's license → click **Renew**
3. Set new expiry date → click **Renew & Generate Token**
4. Copy the new token shown (shown once)
5. Send to client
6. Client visits `php artisan license:admin-token`, opens URL, pastes new token
7. Done — no deployment needed

---

## Part 7: File Structure

```
license-server/                    # Your central server (standard Laravel)
├── app/
│   ├── Models/License.php
│   ├── Models/LicenseEvent.php
│   ├── Services/TokenGeneratorService.php
│   ├── Http/Controllers/LicenseController.php
│   └── Console/Commands/
│       ├── GenerateKeyPair.php    # php artisan license:keygen
│       └── IssueLicenseToken.php  # php artisan license:issue
├── resources/views/admin/
│   ├── layout.blade.php
│   ├── index.blade.php
│   ├── show.blade.php
│   └── create.blade.php
└── database/migrations/

laravel-license-package/           # Composer package for client projects
├── src/
│   ├── LicenseServiceProvider.php
│   ├── Services/
│   │   ├── TokenVerifier.php      # Core offline verification
│   │   └── VerificationResult.php
│   ├── Http/
│   │   ├── Middleware/
│   │   │   ├── LicenseCheck.php
│   │   │   └── LicenseAdminGuard.php
│   │   └── Controllers/
│   │       └── LicenseAdminController.php
│   └── Console/Commands/
│       └── GenerateAdminToken.php
├── config/license.php
├── views/
│   ├── expired.blade.php          # Shown when license invalid
│   └── admin/dashboard.blade.php  # Hidden client-side admin
└── composer.json
```
