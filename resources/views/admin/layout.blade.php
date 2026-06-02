<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>@yield('title', 'License Manager') — LicenseOS</title>
    <style>
        :root {
            --bg:        #080c14;
            --surface:   #0d1421;
            --surface2:  #121929;
            --border:    #1e2d45;
            --accent:    #3b82f6;
            --accent2:   #06b6d4;
            --green:     #10b981;
            --red:       #ef4444;
            --yellow:    #f59e0b;
            --text:      #e2e8f0;
            --muted:     #64748b;
            --mono:      'JetBrains Mono', 'Fira Code', monospace;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

        body {
            font-family: 'Space Grotesk', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 220px;
            flex-shrink: 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 1.5rem 0;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .logo {
            padding: 0 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1rem;
        }
        .logo-mark {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--accent);
        }
        .logo-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            margin-top: .2rem;
        }
        .nav-label {
            font-size: .65rem;
            font-weight: 600;
            letter-spacing: .15em;
            text-transform: uppercase;
            color: var(--muted);
            padding: .75rem 1.25rem .4rem;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .55rem 1.25rem;
            color: var(--muted);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            transition: color .15s, background .15s;
            border-left: 2px solid transparent;
        }
        .nav-link:hover { color: var(--text); background: var(--surface2); }
        .nav-link.active { color: var(--accent); border-left-color: var(--accent); background: rgba(59,130,246,.07); }
        .nav-link svg { width: 16px; height: 16px; flex-shrink: 0; }
        .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border);
            font-size: .75rem;
            color: var(--muted);
        }

        /* ── Main ── */
        .main { flex: 1; overflow-x: hidden; }
        .topbar {
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--surface);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .page-title { font-size: 1rem; font-weight: 600; }
        .content { padding: 2rem; max-width: 1200px; }

        /* ── Cards ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .card-title {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1.25rem;
        }

        /* ── Stat Grid ── */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px,1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.25rem;
        }
        .stat-label { font-size: .75rem; color: var(--muted); margin-bottom: .4rem; }
        .stat-value { font-size: 2rem; font-weight: 700; color: var(--text); line-height: 1; }
        .stat-card.accent .stat-value { color: var(--accent); }
        .stat-card.green  .stat-value { color: var(--green); }
        .stat-card.red    .stat-value { color: var(--red); }
        .stat-card.yellow .stat-value { color: var(--yellow); }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .85rem; }
        th { padding: .6rem .75rem; text-align: left; font-size: .7rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--border); white-space: nowrap; }
        td { padding: .75rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,.02); }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .6rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .04em;
        }
        .badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .badge-active   { background: rgba(16,185,129,.12); color: var(--green); }
        .badge-expired  { background: rgba(239,68,68,.12);  color: var(--red); }
        .badge-revoked  { background: rgba(100,116,139,.12);color: var(--muted); }
        .badge-suspended{ background: rgba(245,158,11,.12); color: var(--yellow); }
        .badge-pending  { background: rgba(59,130,246,.12); color: var(--accent); }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem 1rem;
            border-radius: 7px;
            font-size: .85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .15s, transform .1s;
        }
        .btn:active { transform: scale(.98); }
        .btn:hover  { opacity: .9; }
        .btn-primary { background: var(--accent);      color: #fff; }
        .btn-success { background: var(--green);       color: #fff; }
        .btn-danger  { background: var(--red);         color: #fff; }
        .btn-warning { background: var(--yellow);      color: #000; }
        .btn-ghost   { background: var(--surface2);    color: var(--text); border: 1px solid var(--border); }
        .btn-sm      { padding: .3rem .7rem; font-size: .78rem; }

        /* ── Forms ── */
        .form-group  { margin-bottom: 1.25rem; }
        label        { display: block; font-size: .82rem; color: var(--muted); margin-bottom: .4rem; font-weight: 500; }
        input[type=text], input[type=email], input[type=date], select, textarea {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: .6rem .85rem;
            color: var(--text);
            font-size: .9rem;
            font-family: inherit;
            transition: border-color .15s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        textarea { resize: vertical; min-height: 80px; }

        /* ── Alerts ── */
        .alert { padding: .85rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: .875rem; display: flex; align-items: flex-start; gap: .6rem; }
        .alert-success { background: rgba(16,185,129,.1);  border: 1px solid rgba(16,185,129,.25); color: #6ee7b7; }
        .alert-error   { background: rgba(239,68,68,.1);   border: 1px solid rgba(239,68,68,.25);  color: #fca5a5; }
        .alert-info    { background: rgba(59,130,246,.1);  border: 1px solid rgba(59,130,246,.25); color: #93c5fd; }
        .alert-warning { background: rgba(245,158,11,.1);  border: 1px solid rgba(245,158,11,.25); color: #fcd34d; }

        /* ── Token box ── */
        .token-box {
            background: var(--bg);
            border: 1px solid var(--accent);
            border-radius: 8px;
            padding: 1rem;
            font-family: var(--mono);
            font-size: .72rem;
            color: var(--accent2);
            word-break: break-all;
            line-height: 1.6;
            position: relative;
        }
        .copy-btn {
            position: absolute;
            top: .5rem; right: .5rem;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--muted);
            border-radius: 5px;
            padding: .25rem .6rem;
            font-size: .7rem;
            cursor: pointer;
        }
        .copy-btn:hover { color: var(--text); }

        /* ── Mono ── */
        .mono { font-family: var(--mono); font-size: .82rem; color: var(--muted); }

        /* ── Pagination ── */
        .pagination { display: flex; gap: .4rem; justify-content: center; margin-top: 1.5rem; }
        .pagination a, .pagination span {
            padding: .4rem .75rem;
            border-radius: 6px;
            font-size: .82rem;
            border: 1px solid var(--border);
            color: var(--muted);
            text-decoration: none;
        }
        .pagination .active span { background: var(--accent); color: #fff; border-color: var(--accent); }
        .pagination a:hover { border-color: var(--accent); color: var(--accent); }

        /* ── Grid ── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } .sidebar { display: none; } }

        /* ── Divider ── */
        .divider { border: none; border-top: 1px solid var(--border); margin: 1.25rem 0; }

        /* ── Flex utils ── */
        .flex         { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2        { gap: .5rem; }
        .gap-3        { gap: .75rem; }
        .flex-wrap    { flex-wrap: wrap; }
        .mt-1         { margin-top: .25rem; }
        .mb-4         { margin-bottom: 1rem; }
        .text-sm      { font-size: .85rem; }
        .text-xs      { font-size: .75rem; }
        .text-muted   { color: var(--muted); }
        .text-green   { color: var(--green); }
        .text-red     { color: var(--red); }
        .text-yellow  { color: var(--yellow); }
        .text-accent  { color: var(--accent); }

        .form-inline { display: flex; gap: .75rem; align-items: flex-end; flex-wrap: wrap; }
        .form-inline .form-group { margin-bottom: 0; }
        .form-inline input { width: 200px; }

        .actions-cell form { display: inline; }
        .actions-cell { white-space: nowrap; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo">
        <div class="logo-mark">⬡ LicenseOS</div>
        <div class="logo-name">Admin Panel</div>
    </div>

    <span class="nav-label">Management</span>
    <a href="{{ route('licenses.index') }}" class="nav-link {{ request()->routeIs('licenses.index') ? 'active' : '' }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        All Licenses
    </a>
    <a href="{{ route('licenses.create') }}" class="nav-link {{ request()->routeIs('licenses.create') ? 'active' : '' }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New License
    </a>

    <div class="sidebar-footer">
        Laravel 11 · PHP 8.3<br>
        Ed25519 Offline Tokens
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div class="page-title">@yield('title', 'Dashboard')</div>
        <div class="flex gap-2 items-center">
            <span class="text-xs text-muted">{{ now()->format('d M Y, H:i') }}</span>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('lf').submit();" class="btn btn-ghost btn-sm">Logout</a>
            <form id="lf" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
        </div>
    </div>

    <div class="content">
        @if(session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">✗ {{ session('error') }}</div>
        @endif

        @if(session('token'))
            <div class="card" style="border-color: var(--accent);">
                <div class="card-title" style="color: var(--accent);">⚡ New Token Generated — Copy Now</div>
                <div class="alert alert-warning mb-4" style="margin-bottom: 1rem;">
                    ⚠ This token is shown only once. Copy it and send to the client via secure channel. Paste it in their <code>.env</code> as <code>LICENSE_TOKEN=...</code>
                </div>
                <div class="token-box" id="token-display">
                    {{ session('token') }}
                    <button class="copy-btn" onclick="copyToken()">Copy</button>
                </div>
                <div style="margin-top:.75rem;">
                    <code class="mono" style="font-size: .75rem;">LICENSE_TOKEN={{ session('token') }}</code>
                </div>
            </div>
            <script>
                function copyToken() {
                    navigator.clipboard.writeText(document.getElementById('token-display').innerText.replace('Copy','').trim());
                    document.querySelector('.copy-btn').textContent = 'Copied!';
                    setTimeout(() => document.querySelector('.copy-btn').textContent = 'Copy', 2000);
                }
            </script>
        @endif

        @yield('content')
    </div>
</div>

</body>
</html>
