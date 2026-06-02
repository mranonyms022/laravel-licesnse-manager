<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>License Manager — Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #030712;
            --surface: #0d1117;
            --border:  #1e2d3d;
            --accent:  #3b82f6;
            --red:     #ef4444;
            --text:    #e2e8f0;
            --muted:   #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 800px 500px at 50% -20%, rgba(59,130,246,.12) 0%, transparent 70%);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.015) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        .wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            padding: 1.5rem;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 52px;
            height: 52px;
            background: rgba(59,130,246,.12);
            border: 1px solid rgba(59,130,246,.25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto .75rem;
            font-size: 22px;
        }

        .logo-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
        }

        .logo-sub {
            font-size: .78rem;
            color: var(--muted);
            margin-top: .2rem;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 2rem;
        }

        .card-title {
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1.5rem;
        }

        .alert {
            padding: .75rem 1rem;
            border-radius: 8px;
            font-size: .82rem;
            margin-bottom: 1.25rem;
        }
        .alert-error {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.2);
            color: #fca5a5;
        }
        .alert-info {
            background: rgba(59,130,246,.1);
            border: 1px solid rgba(59,130,246,.2);
            color: #93c5fd;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        label {
            display: block;
            font-size: .78rem;
            color: var(--muted);
            font-weight: 500;
            margin-bottom: .4rem;
        }

        input[type=email],
        input[type=password] {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .65rem .9rem;
            color: var(--text);
            font-size: .9rem;
            font-family: inherit;
            transition: border-color .15s, box-shadow .15s;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59,130,246,.12);
        }

        input.error {
            border-color: var(--red);
        }

        .field-error {
            font-size: .75rem;
            color: #fca5a5;
            margin-top: .3rem;
        }

        .btn {
            width: 100%;
            padding: .7rem;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            margin-top: .5rem;
            transition: opacity .15s, transform .1s;
        }

        .btn:hover   { opacity: .9; }
        .btn:active  { transform: scale(.98); }

        .footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .72rem;
            color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body>

<div class="wrap">

    <div class="logo">
        <div class="logo-icon">⬡</div>
        <div class="logo-title">License Manager</div>
        <div class="logo-sub">Admin Panel</div>
    </div>

    <div class="card">
        <div class="card-title">Sign in to continue</div>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label>Email Address</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="admin@yourcompany.com"
                    autocomplete="email"
                    autofocus
                    class="{{ $errors->has('email') ? 'error' : '' }}"
                >
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="••••••••••"
                    autocomplete="current-password"
                    class="{{ $errors->has('password') ? 'error' : '' }}"
                >
            </div>

            <button type="submit" class="btn">Sign In →</button>
        </form>
    </div>

    <div class="footer">
        Laravel 11 · Ed25519 · Offline Verification
    </div>

</div>

</body>
</html>
