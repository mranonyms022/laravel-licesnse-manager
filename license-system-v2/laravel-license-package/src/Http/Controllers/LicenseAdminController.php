<?php

namespace YourVendor\LaravelLicense\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use YourVendor\LaravelLicense\Services\TokenVerifier;
use YourVendor\LaravelLicense\Services\VerificationResult;

class LicenseAdminController extends Controller
{
    public function __construct(private TokenVerifier $verifier) {}

    public function dashboard(): \Illuminate\View\View
    {
        $status = $this->buildStatusArray();
        return view('license::admin.dashboard', ['licenseStatus' => $status]);
    }

    public function updateToken(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'min:50'],
        ]);

        $token = trim($request->input('token'));

        // Verify the new token before saving
        // Temporarily put it in config and test
        config(['license.token' => $token]);
        Cache::forget('lic_verify_result');

        $result = $this->verifier->verify();

        if (! $result->valid) {
            return back()->with('error', 'Token verification failed: ' . str_replace('_', ' ', $result->reason) . '. Token NOT saved.');
        }

        // Save to storage file
        file_put_contents(storage_path('app/.lic'), $token);
        chmod(storage_path('app/.lic'), 0600);

        // Clear cache
        Cache::forget('lic_verify_result');
        Cache::forget('lic_daily_status');

        return back()->with('success', 'Token verified and saved. License is active until ' . $result->expiresAt()?->format('d M Y') . '.');
    }

    public function clearCache(): \Illuminate\Http\RedirectResponse
    {
        Cache::forget('lic_verify_result');
        Cache::forget('lic_daily_status');

        // Force fresh verification
        $result = $this->verifier->verify();

        $msg = $result->valid
            ? 'Cache cleared. Token is valid. Expires: ' . $result->expiresAt()?->format('d M Y')
            : 'Cache cleared. Warning: Token invalid — ' . $result->reason;

        return back()->with($result->valid ? 'success' : 'error', $msg);
    }

    public function statusJson(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->buildStatusArray());
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->session()->forget(['lic_admin_authed', 'lic_admin_authed_at']);
        return redirect('/');
    }

    private function buildStatusArray(): array
    {
        $result = $this->verifier->verify();
        $payload = $result->payload;

        $daysLeft = null;
        if ($result->expiresAt()) {
            $daysLeft = max(0, (int) (($result->expiresAt()->getTimestamp() - time()) / 86400));
        }

        return [
            'valid'        => $result->valid,
            'in_grace'     => $result->inGracePeriod,
            'reason'       => $result->reason,
            'domain'       => $result->domain(),
            'client_name'  => $result->clientName(),
            'expires_at'   => $result->expiresAt()?->format('d M Y'),
            'days_left'    => $daysLeft,
            'grace_days'   => $payload['grc'] ?? 0,
            'product'      => $payload['prd'] ?? null,
            'features'     => $result->features(),
            'token_source' => config('license.token') ? '.env → LICENSE_TOKEN' : 'storage/app/.lic',
            'checked_at'   => now()->format('d M Y, H:i'),
        ];
    }
}
