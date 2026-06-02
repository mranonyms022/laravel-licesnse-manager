<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') — License Manager</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #030712;
            --s1:       #0d1117;
            --s2:       #111827;
            --s3:       #1a2332;
            --border:   #1e2d3d;
            --accent:   #3b82f6;
            --green:    #10b981;
            --red:      #ef4444;
            --yellow:   #f59e0b;
            --orange:   #f97316;
            --text:     #e2e8f0;
            --muted:    #64748b;
            --mono:     'JetBrains Mono', monospace;
            --sans:     'Inter', sans-serif;
        }

        body {
            font-family: var(--sans);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            font-size: 14px;
            line-height: 1.5;
        }

        /* ── Sidebar ─────────────────────────────────────────────────── */
        .sidebar {
            width: 240px;
            flex-shrink: 0;
            background: var(--s1);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .sidebar-logo-icon {
            width: 34px;
            height: 34px;
            background: rgba(59,130,246,.15);
            border: 1px solid rgba(59,130,246,.3);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .sidebar-logo-text {
            font-size: .875rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .sidebar-logo-sub {
            font-size: .68rem;
            color: var(--muted);
        }

        .nav-section {
            padding: 1rem 0 .5rem;
        }

        .nav-label {
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0 1.5rem .5rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .55rem 1.5rem;
            color: var(--muted);
            text-decoration: none;
            font-size: .825rem;
            font-weight: 500;
            border-left: 2px solid transparent;
            transition: all .15s;
        }

        .nav-item:hover {
            color: var(--text);
            background: rgba(255,255,255,.04);
        }

        .nav-item.active {
            color: var(--accent);
            background: rgba(59,130,246,.07);
            border-left-color: var(--accent);
        }

        .nav-item svg { width: 15px; height: 15px; flex-shrink: 0; }

        .nav-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: .5rem 0;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
        }

        .sidebar-footer-user {
            font-size: .78rem;
            color: var(--muted);
            margin-bottom: .6rem;
        }

        .sidebar-footer-user span {
            color: var(--text);
            font-weight: 500;
        }

        /* ── Main ────────────────────────────────────────────────────── */
        .main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

        .topbar {
            background: var(--s1);
            border-bottom: 1px solid var(--border);
            padding: .875rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .breadcrumb {
            font-size: .8rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .breadcrumb-sep { color: var(--border); }
        .breadcrumb-current { color: var(--text); font-weight: 500; }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .topbar-time {
            font-size: .75rem;
            color: var(--muted);
            font-family: var(--mono);
        }

        .content {
            padding: 2rem;
            flex: 1;
        }

        /* ── Cards ───────────────────────────────────────────────────── */
        .card {
            background: var(--s1);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-head {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1.25rem;
        }

        /* ── Stat Cards ──────────────────────────────────────────────── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--s1);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
        }

        .stat-card.blue::before   { background: var(--accent); }
        .stat-card.green::before  { background: var(--green); }
        .stat-card.red::before    { background: var(--red); }
        .stat-card.yellow::before { background: var(--yellow); }

        .stat-label { font-size: .72rem; color: var(--muted); margin-bottom: .5rem; }
        .stat-value { font-size: 2.2rem; font-weight: 700; line-height: 1; }
        .stat-card.blue .stat-value   { color: var(--accent); }
        .stat-card.green .stat-value  { color: var(--green); }
        .stat-card.red .stat-value    { color: var(--red); }
        .stat-card.yellow .stat-value { color: var(--yellow); }
        .stat-sub { font-size: .72rem; color: var(--muted); margin-top: .35rem; }

        /* ── Table ───────────────────────────────────────────────────── */
        .table-wrap { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; font-size: .825rem; }

        th {
            padding: .65rem .875rem;
            text-align: left;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        td {
            padding: .875rem;
            border-bottom: 1px solid rgba(30,45,61,.5);
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,.02); }

        /* ── Badges ──────────────────────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .65rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .03em;
            white-space: nowrap;
        }

        .badge::before {
            content: '';
            width: 5px; height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .badge-active    { background: rgba(16,185,129,.1);  color: #34d399; }
        .badge-expired   { background: rgba(239,68,68,.1);   color: #f87171; }
        .badge-revoked   { background: rgba(100,116,139,.1); color: #94a3b8; }
        .badge-suspended { background: rgba(245,158,11,.1);  color: #fbbf24; }
        .badge-pending   { background: rgba(59,130,246,.1);  color: #60a5fa; }

        /* ── Buttons ─────────────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem 1rem;
            border-radius: 8px;
            font-size: .82rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: var(--sans);
            transition: opacity .15s, transform .1s;
            white-space: nowrap;
        }

        .btn:hover  { opacity: .88; }
        .btn:active { transform: scale(.97); }

        .btn-primary  { background: var(--accent);              color: #fff; }
        .btn-success  { background: var(--green);               color: #fff; }
        .btn-danger   { background: var(--red);                 color: #fff; }
        .btn-warning  { background: var(--yellow);              color: #000; }
        .btn-ghost    { background: var(--s2); border: 1px solid var(--border); color: var(--text); }
        .btn-sm       { padding: .3rem .7rem; font-size: .75rem; border-radius: 6px; }
        .btn-icon     { padding: .35rem; }

        /* ── Forms ───────────────────────────────────────────────────── */
        .form-group  { margin-bottom: 1.1rem; }

        label {
            display: block;
            font-size: .78rem;
            color: var(--muted);
            font-weight: 500;
            margin-bottom: .4rem;
        }

        input[type=text],
        input[type=email],
        input[type=date],
        input[type=number],
        select,
        textarea {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .6rem .85rem;
            color: var(--text);
            font-size: .875rem;
            font-family: var(--sans);
            transition: border-color .15s, box-shadow .15s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59,130,246,.12);
        }

        textarea { resize: vertical; min-height: 80px; }
        select { cursor: pointer; }

        .field-error {
            font-size: .72rem;
            color: #f87171;
            margin-top: .3rem;
        }

        /* ── Grid ────────────────────────────────────────────────────── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; }

        @media (max-width: 900px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }

        /* ── Alerts ──────────────────────────────────────────────────── */
        .alert {
            padding: .875rem 1rem;
            border-radius: 8px;
            font-size: .825rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: .6rem;
        }

        .alert-success { background: rgba(16,185,129,.08);  border: 1px solid rgba(16,185,129,.2);  color: #6ee7b7; }
        .alert-error   { background: rgba(239,68,68,.08);   border: 1px solid rgba(239,68,68,.2);   color: #fca5a5; }
        .alert-warning { background: rgba(245,158,11,.08);  border: 1px solid rgba(245,158,11,.2);  color: #fcd34d; }
        .alert-info    { background: rgba(59,130,246,.08);  border: 1px solid rgba(59,130,246,.2);  color: #93c5fd; }

        /* ── Token Box ───────────────────────────────────────────────── */
        .token-box {
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
            top: .6rem; right: .6rem;
            background: var(--s2);
            border: 1px solid var(--border);
            color: var(--muted);
            border-radius: 5px;
            padding: .2rem .6rem;
            font-size: .68rem;
            cursor: pointer;
            font-family: var(--sans);
            transition: color .15s;
        }

        .copy-btn:hover { color: var(--text); }

        /* ── Misc ────────────────────────────────────────────────────── */
        .mono     { font-family: var(--mono); font-size: .8rem; color: var(--muted); }
        .divider  { border: none; border-top: 1px solid var(--border); margin: 1.25rem 0; }
        .flex     { display: flex; }
        .flex-wrap{ flex-wrap: wrap; }
        .gap-2    { gap: .5rem; }
        .gap-3    { gap: .75rem; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .mt-1 { margin-top: .25rem; }
        .mt-2 { margin-top: .5rem; }
        .mb-3 { margin-bottom: .75rem; }
        .mb-4 { margin-bottom: 1rem; }
        .text-sm   { font-size: .825rem; }
        .text-xs   { font-size: .72rem; }
        .text-muted{ color: var(--muted); }
        .text-green{ color: var(--green); }
        .text-red  { color: var(--red); }
        .text-yellow{ color: var(--yellow); }
        .text-accent{ color: var(--accent); }
        .text-white { color: #fff; }
        .fw-600    { font-weight: 600; }
        .w-full    { width: 100%; }

        .actions-cell { white-space: nowrap; }
        .actions-cell form { display: inline; }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
        }

        .page-sub {
            font-size: .78rem;
            color: var(--muted);
            margin-top: .2rem;
        }

        /* Search */
        .search-bar {
            display: flex;
            gap: .75rem;
            align-items: flex-end;
            flex-wrap: wrap;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .search-bar .form-group { margin-bottom: 0; }
        .search-bar input { width: 220px; }
        .search-bar select { width: 150px; }

        /* Expiry warning */
        .expiry-warn { color: var(--yellow); }
        .expiry-crit { color: var(--red); }

        /* Pagination */
        .pagination {
            display: flex;
            gap: .35rem;
            justify-content: center;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
        }

        .pagination a,
        .pagination span {
            padding: .35rem .7rem;
            border-radius: 6px;
            font-size: .78rem;
            border: 1px solid var(--border);
            color: var(--muted);
            text-decoration: none;
            transition: all .15s;
        }

        .pagination a:hover { border-color: var(--accent); color: var(--accent); }
        .pagination .active span {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--muted);
        }

        .empty-state-icon { font-size: 2.5rem; margin-bottom: 1rem; opacity: .4; }
        .empty-state-title { font-size: .9rem; font-weight: 600; color: var(--text); margin-bottom: .4rem; }
        .empty-state-sub { font-size: .78rem; }

        /* Detail table */
        .detail-table { width: 100%; border-collapse: collapse; font-size: .825rem; }
        .detail-table td { padding: .65rem .5rem; border-bottom: 1px solid rgba(30,45,61,.5); }
        .detail-table td:first-child { color: var(--muted); width: 38%; font-size: .78rem; }
        .detail-table tr:last-child td { border-bottom: none; }

        /* Event timeline */
        .event-item {
            display: flex;
            gap: .75rem;
            padding: .75rem 0;
            border-bottom: 1px solid rgba(30,45,61,.5);
        }
        .event-item:last-child { border-bottom: none; }
        .event-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--accent);
            margin-top: .35rem;
            flex-shrink: 0;
        }
    </style>
</head>
<body>

{{-- ── Sidebar ── --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">⬡</div>
        <div>
            <div class="sidebar-logo-text">LicenseOS</div>
            <div class="sidebar-logo-sub">Admin Panel</div>
        </div>
    </div>

    <div class="nav-section">
        <div class="nav-label">Licenses</div>

        <a href="{{ route('licenses.index') }}"
           class="nav-item {{ request()->routeIs('licenses.index') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            All Licenses
        </a>

        <a href="{{ route('licenses.create') }}"
           class="nav-item {{ request()->routeIs('licenses.create') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New License
        </a>
    </div>

    <hr class="nav-divider">

    <div class="nav-section">
        <div class="nav-label">Quick Filters</div>
        <a href="{{ route('licenses.index', ['status' => 'active']) }}" class="nav-item">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Active
        </a>
        <a href="{{ route('licenses.index', ['status' => 'expired']) }}" class="nav-item">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Expired
        </a>
        <a href="{{ route('licenses.index', ['status' => 'suspended']) }}" class="nav-item">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Suspended
        </a>
        <a href="{{ route('licenses.index', ['status' => 'revoked']) }}" class="nav-item">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            Revoked
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-footer-user">
            Logged in as<br><span>{{ config('admin.email') }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm w-full">Sign Out</button>
        </form>
    </div>
</aside>

{{-- ── Main ── --}}
<div class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div class="breadcrumb">
                <span>LicenseOS</span>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">@yield('title', 'Dashboard')</span>
            </div>
        </div>
        <div class="topbar-right">
            <span class="topbar-time">{{ now()->format('d M Y, H:i') }}</span>
        </div>
    </div>

    <div class="content">

        @if(session('success'))
            <div class="alert alert-success">✓ &nbsp;{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">✗ &nbsp;{{ session('error') }}</div>
        @endif

        {{-- Token Flash --}}
        @if(session('token'))
            <div class="card" style="border-color: rgba(59,130,246,.4); margin-bottom: 1.5rem;">
                <div class="card-head" style="color: var(--accent);">⚡ New Token Generated — Copy Now</div>
                <div class="alert alert-warning mb-4">
                    ⚠ &nbsp;Token shown only once. Copy and send to client via secure channel.
                    They paste it in <code style="font-family:var(--mono); background:rgba(255,255,255,.08); padding:.1rem .4rem; border-radius:4px;">.env</code> as <code style="font-family:var(--mono); background:rgba(255,255,255,.08); padding:.1rem .4rem; border-radius:4px;">LICENSE_TOKEN=...</code>
                </div>
                <div class="token-box" id="token-box">
                    {{ session('token') }}
                    <button class="copy-btn" onclick="copyToken()">Copy</button>
                </div>
                <div style="margin-top: .75rem;">
                    <span class="mono" style="color: var(--muted);">LICENSE_TOKEN=</span><span class="mono" style="color: #7dd3fc; font-size: .7rem;">{{ session('token') }}</span>
                </div>
            </div>
            <script>
                function copyToken() {
                    const text = document.getElementById('token-box').innerText.replace('Copy','').trim();
                    navigator.clipboard.writeText(text);
                    document.querySelector('.copy-btn').textContent = '✓ Copied';
                    setTimeout(() => document.querySelector('.copy-btn').textContent = 'Copy', 2000);
                }
            </script>
        @endif

        @yield('content')
    </div>
</div>

</body>
</html>
