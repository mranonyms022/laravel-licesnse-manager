<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <title>License · Management</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #0a0d14;
            --s1:      #0f1520;
            --s2:      #141c2e;
            --border:  #1e2d45;
            --accent:  #3b82f6;
            --green:   #10b981;
            --red:     #ef4444;
            --yellow:  #f59e0b;
            --text:    #e2e8f0;
            --muted:   #64748b;
            --mono:    'IBM Plex Mono', monospace;
            --sans:    'IBM Plex Sans', sans-serif;
        }

        body {
            font-family: var(--sans);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 2rem;
        }

        .wrap { max-width: 720px; margin: 0 auto; }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
        }
        .logo { font-size: .7rem; letter-spacing: .15em; text-transform: uppercase; color: var(--muted); }
        .logo span { color: var(--accent); }
        .header-actions { display: flex; gap: .5rem; }

        /* Cards */
        .card {
            background: var(--s1);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }
        .card-head {
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1.25rem;
        }

        /* Status row */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat {
            background: var(--s2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
        }
        .stat-label { font-size: .7rem; color: var(--muted); margin-bottom: .4rem; }
        .stat-value { font-size: 1.1rem; font-weight: 600; }
        .stat-value.green  { color: var(--green); }
        .stat-value.red    { color: var(--red); }
        .stat-value.yellow { color: var(--yellow); }
        .stat-value.accent { color: var(--accent); }

        /* Badge */
        .badge {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .2rem .65rem; border-radius: 999px;
            font-size: .72rem; font-weight: 600;
        }
        .badge::before { content:''; width:5px; height:5px; border-radius:50%; background:currentColor; }
        .badge-valid   { background: rgba(16,185,129,.12); color: var(--green); }
        .badge-grace   { background: rgba(245,158,11,.12); color: var(--yellow); }
        .badge-invalid { background: rgba(239,68,68,.12);  color: var(--red); }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .5rem 1rem; border-radius: 7px;
            font-size: .82rem; font-weight: 600;
            border: none; cursor: pointer; text-decoration: none;
            transition: opacity .15s;
        }
        .btn:hover { opacity: .85; }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-ghost   { background: var(--s2); color: var(--text); border: 1px solid var(--border); }
        .btn-danger  { background: rgba(239,68,68,.15); color: var(--red); border: 1px solid rgba(239,68,68,.25); }
        .btn-sm      { padding: .3rem .7rem; font-size: .75rem; }

        /* Form */
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: .78rem; color: var(--muted); margin-bottom: .4rem; }
        textarea, input[type=text] {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: .65rem .85rem;
            color: var(--text);
            font-family: var(--mono);
            font-size: .78rem;
            resize: vertical;
            transition: border-color .15s;
        }
        textarea:focus, input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59,130,246,.12);
        }
        textarea { min-height: 100px; }

        /* Token display */
        .token-display {
            background: var(--bg);
            border: 1px solid var(--accent);
            border-radius: 8px;
            padding: 1rem;
            font-family: var(--mono);
            font-size: .72rem;
            color: #7dd3fc;
            word-break: break-all;
            line-height: 1.7;
            position: relative;
        }
        .copy-btn {
            position: absolute;
            top: .5rem; right: .5rem;
            background: var(--s2); border: 1px solid var(--border);
            color: var(--muted); border-radius: 5px;
            padding: .2rem .55rem; font-size: .68rem;
            cursor: pointer; font-family: var(--mono);
        }
        .copy-btn:hover { color: var(--text); }

        /* Info table */
        .info-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
        .info-table td { padding: .55rem .5rem; border-bottom: 1px solid var(--border); }
        .info-table td:first-child { color: var(--muted); width: 40%; }
        .info-table tr:last-child td { border-bottom: none; }
        .mono { font-family: var(--mono); font-size: .78rem; }

        /* Alerts */
        .alert { padding: .75rem 1rem; border-radius: 7px; font-size: .82rem; margin-bottom: 1rem; }
        .alert-success { background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.2); color: #6ee7b7; }
        .alert-error   { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.2);  color: #fca5a5; }
        .alert-info    { background: rgba(59,130,246,.1); border: 1px solid rgba(59,130,246,.2); color: #93c5fd; }
        .alert-warning { background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.2); color: #fcd34d; }

        .divider { border: none; border-top: 1px solid var(--border); margin: 1.25rem 0; }
        .text-muted { color: var(--muted); }
        .text-xs { font-size: .75rem; }
        .mt-2 { margin-top: .5rem; }
        .flex { display: flex; }
        .gap-2 { gap: .5rem; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .flex-wrap { flex-wrap: wrap; }
    </style>
</head>
<body>

<div class="wrap">

    <div class="header">
        <div class="logo">⬡ <span>License</span> — Management Console</div>
        <div class="header-actions">
            <a href="{{ route('license.admin.logout') }}" class="btn btn-ghost btn-sm"
               onclick="return confirm('End this session?')">End Session</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif

    {{-- Status Overview --}}
    <div class="status-grid">
        @php
            $status = $licenseStatus;
            $valid  = $status['valid'] ?? false;
            $grace  = $status['in_grace'] ?? false;
        @endphp
        <div class="stat">
            <div class="stat-label">Status</div>
            <div class="stat-value">
                @if($valid && !$grace)
                    <span class="badge badge-valid">Active</span>
                @elseif($valid && $grace)
                    <span class="badge badge-grace">Grace Period</span>
                @else
                    <span class="badge badge-invalid">{{ $status['reason'] ?? 'Invalid' }}</span>
                @endif
            </div>
        </div>
        <div class="stat">
            <div class="stat-label">Domain Bound</div>
            <div class="stat-value mono" style="font-size:.85rem;">
                {{ $status['domain'] ?? '—' }}
            </div>
        </div>
        <div class="stat">
            <div class="stat-label">Expires</div>
            <div class="stat-value {{ ($status['days_left'] ?? 999) <= 7 ? 'yellow' : 'green' }}" style="font-size:.85rem;">
                {{ $status['expires_at'] ?? '—' }}
            </div>
        </div>
        <div class="stat">
            <div class="stat-label">Last Checked</div>
            <div class="stat-value accent" style="font-size:.82rem;">
                {{ $status['checked_at'] ?? 'Now' }}
            </div>
        </div>
    </div>

    {{-- Token file info --}}
    <div class="card">
        <div class="card-head">Current Token Info</div>
        <table class="info-table">
            <tr>
                <td>Token Source</td>
                <td class="mono">{{ $status['token_source'] ?? '.env → LICENSE_TOKEN' }}</td>
            </tr>
            <tr>
                <td>Client Name</td>
                <td>{{ $status['client_name'] ?? '—' }}</td>
            </tr>
            <tr>
                <td>Token Expires</td>
                <td class="{{ ($status['days_left'] ?? 999) <= 7 ? 'text-yellow' : '' }}">{{ $status['expires_at'] ?? '—' }}</td>
            </tr>
            <tr>
                <td>Grace Period</td>
                <td>{{ $status['grace_days'] ?? 0 }} days</td>
            </tr>
            <tr>
                <td>Product</td>
                <td>{{ $status['product'] ?? '—' }}</td>
            </tr>
            <tr>
                <td>Features</td>
                <td class="mono text-xs">{{ !empty($status['features']) ? json_encode($status['features']) : '—' }}</td>
            </tr>
            <tr>
                <td>Reason</td>
                <td class="mono {{ $valid ? '' : 'text-red' }}">{{ $status['reason'] ?? '—' }}</td>
            </tr>
        </table>
    </div>

    {{-- Update Token --}}
    <div class="card">
        <div class="card-head">Update License Token</div>
        <div class="alert alert-info" style="margin-bottom: 1rem;">
            Paste the new token received from your license provider. It will be saved to <code>.env</code> and verified immediately.
        </div>

        <form method="POST" action="{{ route('license.admin.update-token') }}">
            @csrf
            <div class="form-group">
                <label>New License Token</label>
                <textarea name="token" placeholder="eyJhbGciOiJFZERTQSIsInR5cCI6IkxJQyJ9..."></textarea>
                @error('token')<div class="text-xs" style="color: var(--red); margin-top: .3rem;">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary">Save & Verify Token</button>
        </form>
    </div>

    {{-- Cache Actions --}}
    <div class="card">
        <div class="card-head">Actions</div>
        <div class="flex gap-2 flex-wrap">
            <form method="POST" action="{{ route('license.admin.clear-cache') }}">
                @csrf
                <button class="btn btn-ghost btn-sm">↺ Re-verify Now</button>
            </form>
            <a href="{{ route('license.admin.status-json') }}" class="btn btn-ghost btn-sm" target="_blank">{ } Raw Status JSON</a>
        </div>
        <div class="text-xs text-muted mt-2">
            Re-verify clears the cache and runs a fresh offline token check without waiting for the next scheduled run.
        </div>
    </div>

    {{-- Security Info --}}
    <div class="card" style="border-color: rgba(59,130,246,.2);">
        <div class="card-head" style="color: var(--accent);">How This Works</div>
        <div class="text-xs" style="color: var(--muted); line-height: 1.8;">
            Token verification is fully <strong style="color:var(--text);">offline</strong> — no external requests. The token contains an Ed25519 cryptographic signature made with your provider's private key. This app has only the public key, so it can verify but never forge tokens. Changing the expiry date, domain, or any field in the token will break the signature and be rejected immediately.
        </div>
    </div>

    <div class="text-xs text-muted" style="text-align:center; margin-top: .5rem;">
        Session auto-expires after 30 minutes of inactivity.
    </div>

</div>

</body>
</html>
