<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateKeyPair extends Command
{
    protected $signature   = 'license:keygen';
    protected $description = 'Generate Ed25519 keypair for license token signing';

    public function handle(): void
    {
        $keypair    = sodium_crypto_sign_keypair();
        $publicKey  = base64_encode(sodium_crypto_sign_publickey($keypair));
        $privateKey = base64_encode(sodium_crypto_sign_secretkey($keypair));
        $fprSecret  = bin2hex(random_bytes(32));

        $this->newLine();
        $this->line('<fg=yellow>════════════════════════════════════════════════════════</> ');
        $this->line('<fg=yellow>  LICENSE SYSTEM KEY GENERATION</>');
        $this->line('<fg=yellow>════════════════════════════════════════════════════════</>');
        $this->newLine();

        $this->line('<fg=red>⚠  PRIVATE KEY — Store in server .env ONLY. NEVER share.</>');
        $this->line('<fg=gray>LICENSE_PRIVATE_KEY=</>' . $privateKey);
        $this->newLine();

        $this->line('<fg=green>✓  PUBLIC KEY — Distribute to each client .env</>');
        $this->line('<fg=gray>LICENSE_PUBLIC_KEY=</>' . $publicKey);
        $this->newLine();

        $this->line('<fg=red>⚠  FINGERPRINT SECRET — Server .env ONLY. NEVER share.</>');
        $this->line('<fg=gray>LICENSE_FINGERPRINT_SECRET=</>' . $fprSecret);
        $this->newLine();

        $this->line('<fg=yellow>════════════════════════════════════════════════════════</>');
        $this->line('<fg=gray>Add PRIVATE KEY and FINGERPRINT_SECRET to your server .env</>');
        $this->line('<fg=gray>Distribute only PUBLIC KEY to clients.</>');
        $this->newLine();
    }
}
