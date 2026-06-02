<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Services\TokenGeneratorService;
use Illuminate\Console\Command;

class IssueLicenseToken extends Command
{
    protected $signature = 'license:issue
                            {--domain=     : Client domain (e.g. app.client.com)}
                            {--key=        : License key (auto-generated if omitted)}
                            {--expires=    : Expiry date Y-m-d (default: +1 year)}
                            {--client=     : Client name}
                            {--email=      : Client email}
                            {--grace=3     : Grace period in days}
                            {--product=default : Product name}';

    protected $description = 'Issue a signed license token from the command line';

    public function __construct(private TokenGeneratorService $tokenGen)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $domain  = $this->option('domain') ?: $this->ask('Client domain (e.g. app.client.com)');
        $expires = $this->option('expires') ?: now()->addYear()->format('Y-m-d');
        $key     = $this->option('key') ?: License::generateKey();

        // Create or find license record
        $license = License::updateOrCreate(
            ['license_key' => $key],
            [
                'client_name'       => $this->option('client') ?: $domain,
                'client_email'      => $this->option('email') ?: 'admin@' . $domain,
                'domain'            => $domain,
                'product_name'      => $this->option('product') ?: 'default',
                'status'            => 'active',
                'grace_period_days' => (int) $this->option('grace'),
                'expires_at'        => $expires,
                'activated_at'      => now(),
            ]
        );

        $token = $this->tokenGen->generate($license);

        $this->newLine();
        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->line('<fg=cyan>  LICENSE TOKEN ISSUED</>');
        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->newLine();

        $this->line("<fg=gray>License Key  :</> <fg=yellow>{$license->license_key}</>");
        $this->line("<fg=gray>Domain       :</> {$domain}");
        $this->line("<fg=gray>Expires      :</> {$expires}");
        $this->line("<fg=gray>Grace        :</> {$license->grace_period_days} days");
        $this->newLine();

        $this->line('<fg=green>━━  TOKEN (paste in client .env as LICENSE_TOKEN=)  ━━</>');
        $this->newLine();
        $this->line($token);
        $this->newLine();

        $this->line('<fg=gray>Public key for client:</>');
        $this->line('<fg=gray>LICENSE_PUBLIC_KEY=</>' . config('license.public_key'));
        $this->newLine();
    }
}
