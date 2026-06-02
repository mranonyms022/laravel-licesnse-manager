<?php

namespace App\Console\Commands;

use App\Models\LicenseKeypair;
use Illuminate\Console\Command;

class ListKeyPairs extends Command
{
    protected $signature   = 'license:keys';
    protected $description = 'List all keypairs in database';

    public function handle(): void
    {
        $keys = LicenseKeypair::orderBy('id')->get();

        if ($keys->isEmpty()) {
            $this->warn('No keypairs found. Run: php artisan license:keygen');
            return;
        }

        $this->newLine();
        $this->table(
            ['Version', 'Status', 'Public Key (first 20 chars)', 'Note', 'Created'],
            $keys->map(fn($k) => [
                $k->version,
                $k->is_active ? '<fg=green>● ACTIVE</>' : '<fg=gray>○ inactive</>',
                substr($k->public_key, 0, 20) . '...',
                $k->note,
                $k->created_at->format('Y-m-d H:i'),
            ])
        );
        $this->newLine();
    }
}
