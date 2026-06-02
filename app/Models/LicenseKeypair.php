<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class LicenseKeypair extends Model
{
    public $timestamps  = false;
    protected $fillable = [
        'version',
        'private_key',
        'public_key',
        'fingerprint_secret',
        'is_active',
        'note',
    ];

    protected $casts = ['is_active' => 'boolean'];

    // ─── Active keypair (token issue ke liye) ────────────────────────
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    // ─── Version se dhundho (old token verify ke liye) ───────────────
    public static function findVersion(string $version): ?self
    {
        return static::where('version', $version)->first();
    }

    // ─── Encrypted getters ────────────────────────────────────────────
    public function getPrivateKeyDecrypted(): string
    {
        return Crypt::decryptString($this->private_key);
    }

    public function getFingerprintSecretDecrypted(): string
    {
        return Crypt::decryptString($this->fingerprint_secret);
    }

    // ─── Next version number ──────────────────────────────────────────
    public static function nextVersion(): string
    {
        $last = static::orderByDesc('id')->first();
        if (! $last) return 'v1';

        $num = (int) ltrim($last->version, 'v');
        return 'v' . ($num + 1);
    }
}
