<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    use HasUuids;

    protected $fillable = [
        'license_key',
        'client_name',
        'client_email',
        'domain',
        'product_name',
        'status',
        'grace_period_days',
        'expires_at',
        'activated_at',
        'features',
        'notes',
    ];

    protected $casts = [
        'expires_at'    => 'datetime',
        'activated_at'  => 'datetime',
        'features'      => 'array',
        'grace_period_days' => 'integer',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(LicenseEvent::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function daysUntilExpiry(): int
    {
        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }

    public function logEvent(string $type, array $payload = []): void
    {
        $this->events()->create([
            'event_type' => $type,
            'ip_address' => request()->ip(),
            'payload'    => $payload,
            'created_at' => now(),
        ]);
    }

    // Generate a unique license key: XXXX-XXXX-XXXX-XXXX
    public static function generateKey(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No 0,O,1,I to avoid confusion
        $key   = '';
        for ($i = 0; $i < 4; $i++) {
            if ($i > 0) $key .= '-';
            for ($j = 0; $j < 4; $j++) {
                $key .= $chars[random_int(0, strlen($chars) - 1)];
            }
        }
        return $key;
    }
}
