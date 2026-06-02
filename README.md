<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<p align="center">
  <strong>License Manager — Admin Panel</strong><br>
  Centralized license management for all your deployed Laravel applications
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/Encryption-Ed25519-green?style=flat" alt="Ed25519">
  <img src="https://img.shields.io/badge/License-Proprietary-red?style=flat" alt="Proprietary">
</p>

---

## What is This?

This is the **server-side admin panel** for managing software licenses.
From here you issue, renew, revoke, and track licenses for all your client deployments.

Clients install the companion package → [laravel-license](https://github.com/mranonyms022/laravel-license)

---

## How It Works

```
Admin Panel (this repo)
      │
      ├── Generate Ed25519 keypair (once)
      │
      ├── Create license → Issue signed token
      │         │
      │         └── Send token to client
      │                   │
      │                   └── Client pastes in .env
      │                             │
      │                             └── Verified OFFLINE
      │                                 No API calls
      │                                 No network needed
      │
      └── Renew / Revoke / Suspend anytime
```

---

## Features

- **Offline Token Generation** — Ed25519 signed JWT tokens, verified without any API call
- **License Management** — Create, renew, suspend, revoke from dashboard
- **Domain Binding** — Every license locked to a specific domain
- **Grace Period** — Configurable buffer days after expiry
- **Versioned Keypairs** — Rotate keys anytime, old tokens never break
- **Event History** — Full audit log of every action per license
- **CLI Support** — Issue and manage licenses via artisan commands

---

## Tech Stack

|            |                                               |
| ---------- | --------------------------------------------- |
| Framework  | Laravel 11                                    |
| Language   | PHP 8.3                                       |
| Database   | MySQL                                         |
| Encryption | Ed25519 (libsodium) + AES-256 (Laravel Crypt) |

---

## Quick Start

```bash
git clone git@github.com:mranonyms022/laravel-license-manager.git
cd laravel-license-manager

composer install
cp .env.example .env

# Database config karo .env mein
php artisan key:generate
php artisan migrate

# Keys generate karo (sirf ek baar)
php artisan license:keygen
```

---

## Artisan Commands

| Command                                                             | Description                   |
| ------------------------------------------------------------------- | ----------------------------- |
| `php artisan license:keygen`                                        | Generate new Ed25519 keypair  |
| `php artisan license:keys`                                          | List all keypairs with status |
| `php artisan license:key-activate v2`                               | Switch active keypair         |
| `php artisan license:issue --domain= --expires= --client= --email=` | Issue token from CLI          |

---

## Keypair Management

```bash
# Pehli baar
php artisan license:keygen

# 6 mahine baad naya key chahiye
php artisan license:keygen
# Old keys preserved — purane tokens valid rahenge

# Sab keys dekhna
php artisan license:keys

# Output:
# +---------+----------+-----------------------------+------------------+
# | Version | Status   | Public Key                  | Created          |
# +---------+----------+-----------------------------+------------------+
# | v1      | inactive | kfmLLR6CsRzMjDFEKImk9i...  | 2024-01-01 10:00 |
# | v2      | ACTIVE   | xK9mP2QrHjLwYbTnSfUoVc...  | 2026-01-01 09:00 |
# +---------+----------+-----------------------------+------------------+
```

---

## Issue a License

**Via Admin Panel:**

1. Dashboard → New License
2. Fill domain, expiry, client details
3. Token auto-generated → copy & send to client

**Via CLI:**

```bash
php artisan license:issue \
  --domain=app.client.com \
  --expires=2026-12-31 \
  --client="Client Name" \
  --email=client@email.com \
  --grace=3
```

---

## Renewal Process

```
1. Dashboard → Find license → Renew
2. Set new expiry → Generate Token
3. Copy token → Send to client
4. Client pastes in .env
5. Done — no deployment needed
```

---

## Security

| What               | How                                            |
| ------------------ | ---------------------------------------------- |
| Private keys       | Encrypted in DB via Laravel Crypt (AES-256)    |
| Token signing      | Ed25519 — cannot be forged without private key |
| Token verification | Offline — no network call on client            |
| Domain binding     | Token invalid on any other domain              |
| Key rotation       | Old keypairs preserved — zero downtime         |

> **Important:** Keep `APP_KEY` and database backup separate.
> Both together can decrypt stored private keys.

---

## Related

- **Client Package** → [mranonyms022/laravel-license](https://github.com/mranonyms022/laravel-license)
