<?php

namespace App\Console\Commands;

use App\Models\LicenseKeypair;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class GenerateKeyPair extends Command
{
    protected $signature   = 'license:keygen {--note= : Optional note for this keypair}';
    protected $description = 'Generate new Ed25519 keypair (old keys preserved)';

    public function handle(): void
    {
        $version = LicenseKeypair::nextVersion();
        $note    = $this->option('note') ?: 'Generated ' . now()->format('Y-m-d H:i');

        // ── Confirm ───────────────────────────────────────────────────
        $current = LicenseKeypair::active();
        if ($current) {
            $this->line("Current active: <fg=yellow>{$current->version}</> ({$current->note})");
            $this->line("New version will be: <fg=green>{$version}</>");
            $this->newLine();

            if (! $this->confirm("Generate {$version} and make it active? Old keys stay in DB.")) {
                $this->line('Aborted.');
                return;
            }
        }

        // ── Generate ──────────────────────────────────────────────────
        $keypair    = sodium_crypto_sign_keypair();
        $publicKey  = base64_encode(sodium_crypto_sign_publickey($keypair));
        $privateKey = base64_encode(sodium_crypto_sign_secretkey($keypair));
        $fprSecret  = bin2hex(random_bytes(32));

        // ── Save to DB ────────────────────────────────────────────────
        DB::transaction(function () use ($version, $publicKey, $privateKey, $fprSecret, $note) {
            // Purane sab inactive
            LicenseKeypair::where('is_active', true)->update(['is_active' => false]);

            // Naya active
            LicenseKeypair::create([
                'version'             => $version,
                'private_key'         => Crypt::encryptString($privateKey),
                'public_key'          => $publicKey, // plain — safe hai
                'fingerprint_secret'  => Crypt::encryptString($fprSecret),
                'is_active'           => true,
                'note'                => $note,
                'created_at'          => now(),
            ]);
        });

        // ── Output ────────────────────────────────────────────────────
        $this->newLine();
        $this->line("<fg=green>✓ Keypair {$version} generated and set as active.</>");
        $this->newLine();
        $this->line('<fg=yellow>Public key — distribute to NEW clients:</>');
        $this->line("LICENSE_PUBLIC_KEY={$publicKey}");
        $this->newLine();
        $this->line('<fg=gray>Old clients keep their old PUBLIC_KEY — still works.</>');
        $this->line('<fg=gray>Private key encrypted in DB. Backup your DB + APP_KEY.</>');
        $this->newLine();
    }
}
