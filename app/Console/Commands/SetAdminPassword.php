<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetAdminPassword extends Command
{
    protected $signature   = 'admin:set-password';
    protected $description = 'Set admin password for the license manager panel';

    public function handle(): void
    {
        $password = $this->secret('Enter new admin password');
        $confirm  = $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('Passwords do not match.');
            return;
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return;
        }

        $hashed = Hash::make($password);

        $this->newLine();
        $this->line('<fg=green>✓ Password hashed. Add to your .env:</>');
        $this->newLine();
        $this->line("ADMIN_PASSWORD={$hashed}");
        $this->newLine();
        $this->line('<fg=gray>Then run: php artisan config:cache</>');
        $this->newLine();
    }
}
