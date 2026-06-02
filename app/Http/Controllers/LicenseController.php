<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Services\TokenGeneratorService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function __construct(private TokenGeneratorService $tokenGen) {}

    // ─── Dashboard ───────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = License::latest();

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('license_key',  'like', "%{$search}%")
                  ->orWhere('client_name',  'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%")
                  ->orWhere('domain',       'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            if ($status === 'expired') {
                $query->where('expires_at', '<', now())
                      ->whereNotIn('status', ['revoked', 'suspended']);
            } else {
                $query->where('status', $status);
            }
        }

        $licenses = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => License::count(),
            'active'   => License::where('status', 'active')->where('expires_at', '>', now())->count(),
            'expired'  => License::where('expires_at', '<', now())->whereNotIn('status', ['revoked','suspended'])->count(),
            'expiring' => License::where('status', 'active')
                                 ->whereBetween('expires_at', [now(), now()->addDays(7)])
                                 ->count(),
        ];

        return view('admin.index', compact('licenses', 'stats'));
    }

    // ─── Create ──────────────────────────────────────────────────────
    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name'       => 'required|string|max:150',
            'client_email'      => 'required|email|max:150',
            'domain'            => 'required|string|max:253',
            'product_name'      => 'required|string|max:100',
            'expires_at'        => 'required|date|after:today',
            'grace_period_days' => 'required|integer|min:0|max:30',
            'notes'             => 'nullable|string',
            'features'          => 'nullable|string',
        ]);

        // Clean domain
        $validated['domain'] = strtolower(
            preg_replace('#^https?://#', '', rtrim($validated['domain'], '/'))
        );

        // Parse features JSON
        $features = null;
        if (! empty($validated['features'])) {
            $features = json_decode($validated['features'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withInput()->withErrors(['features' => 'Invalid JSON format.']);
            }
        }

        $license = License::create([
            ...$validated,
            'license_key'  => License::generateKey(),
            'status'       => 'active',
            'activated_at' => now(),
            'features'     => $features,
        ]);

        $license->logEvent('issued', ['created_by' => 'admin']);

        // Auto-generate token
        try {
            $token = $this->tokenGen->generate($license);
            return redirect()->route('licenses.show', $license)
                             ->with('token', $token)
                             ->with('success', 'License created successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('licenses.show', $license)
                             ->with('error', 'License created but token generation failed: ' . $e->getMessage());
        }
    }

    // ─── Show ─────────────────────────────────────────────────────────
    public function show(License $license)
    {
        $events = $license->events()->latest('created_at')->take(25)->get();
        return view('admin.show', compact('license', 'events'));
    }

    // ─── Generate Token ───────────────────────────────────────────────
    public function generateToken(License $license)
    {
        if ($license->status === 'revoked') {
            return back()->with('error', 'Cannot generate token for a revoked license.');
        }

        try {
            $token = $this->tokenGen->generate($license);
            $license->logEvent('token_generated');
            return back()->with('token', $token)->with('success', 'New token generated.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Token generation failed: ' . $e->getMessage());
        }
    }

    // ─── Renew ────────────────────────────────────────────────────────
    public function renew(Request $request, License $license)
    {
        $request->validate([
            'expires_at' => 'required|date|after:today',
        ]);

        $oldExpiry = $license->expires_at->format('Y-m-d');

        $license->update([
            'expires_at' => $request->expires_at,
            'status'     => 'active',
        ]);

        $license->logEvent('renewed', [
            'old_expires_at' => $oldExpiry,
            'new_expires_at' => $request->expires_at,
        ]);

        try {
            $token = $this->tokenGen->generate($license);
            return back()->with('token', $token)->with('success', 'License renewed. New token generated.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Renewed but token failed: ' . $e->getMessage());
        }
    }

    // ─── Status Actions ───────────────────────────────────────────────
    public function revoke(License $license)
    {
        $license->update(['status' => 'revoked']);
        $license->logEvent('revoked');
        return back()->with('success', 'License revoked. Client app will be blocked immediately.');
    }

    public function suspend(License $license)
    {
        $license->update(['status' => 'suspended']);
        $license->logEvent('suspended');
        return back()->with('success', 'License suspended.');
    }

    public function activate(License $license)
    {
        $license->update(['status' => 'active', 'activated_at' => now()]);
        $license->logEvent('activated');
        return back()->with('success', 'License activated.');
    }

    // ─── Edit ─────────────────────────────────────────────────────────
    public function edit(License $license)
    {
        return view('admin.edit', compact('license'));
    }

    public function update(Request $request, License $license)
    {
        $validated = $request->validate([
            'client_name'       => 'required|string|max:150',
            'client_email'      => 'required|email|max:150',
            'domain'            => 'required|string|max:253',
            'grace_period_days' => 'required|integer|min:0|max:30',
            'notes'             => 'nullable|string',
        ]);

        $validated['domain'] = strtolower(
            preg_replace('#^https?://#', '', rtrim($validated['domain'], '/'))
        );

        $license->update($validated);

        return redirect()->route('licenses.show', $license)
                         ->with('success', 'License updated.');
    }

    // ─── Delete ───────────────────────────────────────────────────────
    public function destroy(License $license)
    {
        $key = $license->license_key;
        $license->delete();

        return redirect()->route('licenses.index')
                         ->with('success', "License {$key} permanently deleted.");
    }
}
