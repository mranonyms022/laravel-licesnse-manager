<?php

namespace YourVendor\LaravelLicense\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GenerateAdminToken extends Command
{
    protected $signature   = 'license:admin-token {--ttl=300 : Token lifetime in seconds}';
    protected $description = 'Generate a one-time URL to access the license admin console';

    public function handle(): void
    {
        $ttl    = (int) $this->option('ttl');
        $token  = Str::random(64); // Cryptographically random

        // Store only the SHA-256 hash — raw token never touches storage
        $cacheKey = 'lic_admin_tok:' . hash('sha256', $token);
        Cache::put($cacheKey, true, $ttl);

        // Build secret path same way ServiceProvider does
        $hash       = hash_hmac('sha256', 'license-admin-path', config('app.key'));
        $secretPath = '/__lic_' . substr($hash, 0, 8);

        $url = url($secretPath . '?_ltoken=' . $token);

        $this->newLine();
        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->line('<fg=cyan>  LICENSE ADMIN ACCESS</>');
        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->newLine();
        $this->line('<fg=yellow>⚠  One-time URL — expires in ' . $ttl . 's, single-use:</>');
        $this->newLine();
        $this->line('<fg=green>' . $url . '</>');
        $this->newLine();
        $this->line('<fg=gray>• Open in your browser immediately</> ');
        $this->line('<fg=gray>• Token is invalidated after first click</>');
        $this->line('<fg=gray>• Session lasts 30 minutes</>');
        $this->line('<fg=gray>• URL changes per APP_KEY rotation</>');
        $this->newLine();
    }
}
