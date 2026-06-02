<?php

namespace App\Console\Commands;

use App\Models\LicenseKeypair;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ActivateKeyPair extends Command
{
    protected $signature   = 'license:key-activate {version}';
    protected $description = 'Switch active keypair to a specific version';

    public function handle(): void
    {
        $version = $this->argument('version');
        $keypair = LicenseKeypair::findVersion($version);

        if (! $keypair) {
            $this->error("Version '{$version}' not found.");
            return;
        }

        if ($keypair->is_active) {
            $this->info("{$version} is already active.");
            return;
        }

        $this->warn("Switching to {$version}. New tokens will use this keypair.");
        $this->line("Clients with tokens from {$version} will still work.");

        if (! $this->confirm('Continue?')) return;

        DB::transaction(function () use ($keypair) {
            LicenseKeypair::where('is_active', true)->update(['is_active' => false]);
            $keypair->update(['is_active' => true]);
        });

        $this->info("✓ {$version} is now active.");
        $this->line("PUBLIC_KEY for new clients: {$keypair->public_key}");
    }
}
