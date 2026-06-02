<?php

namespace YourVendor\LaravelLicense;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use YourVendor\LaravelLicense\Console\Commands\GenerateAdminToken;
use YourVendor\LaravelLicense\Http\Controllers\LicenseAdminController;
use YourVendor\LaravelLicense\Http\Middleware\LicenseAdminGuard;
use YourVendor\LaravelLicense\Http\Middleware\LicenseCheck;
use YourVendor\LaravelLicense\Services\TokenVerifier;

class LicenseServiceProvider extends ServiceProvider
{
    /**
     * SELF_CHECKSUM: Production mein set karo.
     * Generate: php -r "echo hash_file('sha256', __FILE__);"
     * Paste output here, then redeploy package.
     */
    private const SELF_CHECKSUM = null;

    /** Agar ye file missing ho → app boot nahi karegi */
    private const GUARD_FILE = 'bootstrap/license-guard.php';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/license.php', 'license');
        $this->app->singleton(TokenVerifier::class);
    }

    public function boot(): void
    {
        // Self-integrity
        if (self::SELF_CHECKSUM !== null) {
            if (! hash_equals(self::SELF_CHECKSUM, hash_file('sha256', __FILE__))) {
                abort(503, 'Application integrity check failed. Contact support. [SP-002]');
            }
        }

        // Guard file must exist
        if (! file_exists(base_path(self::GUARD_FILE))) {
            if (app()->runningInConsole()) {
                fwrite(STDERR, "[CRITICAL] bootstrap/license-guard.php is missing!\n");
                exit(1);
            }
            abort(503, 'Application configuration error. [LG-001]');
        }

        $this->loadViewsFrom(__DIR__ . '/../views', 'license');
        $this->publishes([__DIR__ . '/../config/license.php' => config_path('license.php')], 'license-config');

        $this->app['router']->aliasMiddleware('license.check', LicenseCheck::class);
        $this->app['router']->aliasMiddleware('license.admin', LicenseAdminGuard::class);

        if ($this->app->runningInConsole()) {
            $this->commands([GenerateAdminToken::class]);
        }

        $this->registerAdminRoutes();

        // Schedule registered HERE, not in Kernel.php
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->call(function () {
                $result = app(TokenVerifier::class)->verify();
                cache()->forget('lic_verify_result');
                @unlink(storage_path('app/.lic_cache')); // clear bootstrap guard cache too
                cache()->put('lic_daily_status', [
                    'valid'      => $result->valid,
                    'in_grace'   => $result->inGracePeriod,
                    'reason'     => $result->reason,
                    'checked_at' => now()->toIso8601String(),
                    'expires_at' => $result->expiresAt()?->format('Y-m-d H:i:s'),
                ], now()->addHours(25));
                if (! $result->valid) {
                    logger()->critical('[License] Daily check FAILED', ['reason' => $result->reason]);
                }
            })->dailyAt('00:05')->name('license.daily.verify')->withoutOverlapping();
        });
    }

    private function registerAdminRoutes(): void
    {
        $hash       = hash_hmac('sha256', 'license-admin-path', config('app.key'));
        $secretPath = '/__lic_' . substr($hash, 0, 8);

        Route::middleware(['web', 'throttle:10,1', 'license.admin'])
            ->prefix($secretPath)
            ->group(function () {
                Route::get('/',           [LicenseAdminController::class, 'dashboard'])->name('license.admin.dashboard');
                Route::post('/token',     [LicenseAdminController::class, 'updateToken'])->name('license.admin.update-token');
                Route::post('/clear-cache',[LicenseAdminController::class, 'clearCache'])->name('license.admin.clear-cache');
                Route::get('/status.json',[LicenseAdminController::class, 'statusJson'])->name('license.admin.status-json');
                Route::get('/logout',     [LicenseAdminController::class, 'logout'])->name('license.admin.logout');
            });
    }
}
