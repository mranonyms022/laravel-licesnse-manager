<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>{{ $app_name ?? 'Application' }} — License Expired</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #03060f;
            --surface: rgba(255,255,255,.04);
            --border:  rgba(255,255,255,.07);
            --red:     #ff4d4d;
            --muted:   rgba(255,255,255,.35);
            --text:    rgba(255,255,255,.85);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Ambient background effect */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 600px 400px at 50% -10%, rgba(220,38,38,.18) 0%, transparent 70%),
                radial-gradient(ellipse 400px 300px at 80% 100%, rgba(220,38,38,.08) 0%, transparent 60%);
            pointer-events: none;
        }

        /* Subtle grid */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.02) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2.5rem;
            max-width: 520px;
            width: 100%;
        }

        /* Icon */
        .icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            background: rgba(220,38,38,.12);
            border: 1px solid rgba(220,38,38,.25);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.75rem;
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,.3); }
            50% { box-shadow: 0 0 0 12px rgba(220,38,38,.0); }
        }

        .icon-wrap svg {
            width: 32px;
            height: 32px;
            color: var(--red);
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .85rem;
            border-radius: 999px;
            background: rgba(220,38,38,.1);
            border: 1px solid rgba(220,38,38,.2);
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--red);
            margin-bottom: 1.25rem;
        }
        .badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--red);
        }

        /* Headings */
        h1 {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 600;
            color: #fff;
            letter-spacing: -.02em;
            line-height: 1.2;
            margin-bottom: .75rem;
        }

        .app-name {
            color: rgba(255,255,255,.4);
            font-size: .9rem;
            font-weight: 400;
            margin-bottom: 2rem;
        }

        /* Card */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.75rem;
            margin-bottom: 1.25rem;
            backdrop-filter: blur(10px);
        }

        .card-label {
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1.1rem;
        }

        /* Contact items */
        .contacts { display: flex; flex-direction: column; gap: .75rem; }
        .contact-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem 1rem;
            background: rgba(255,255,255,.03);
            border: 1px solid var(--border);
            border-radius: 9px;
            text-decoration: none;
            color: var(--text);
            transition: background .2s, border-color .2s;
        }
        .contact-item:hover {
            background: rgba(255,255,255,.07);
            border-color: rgba(255,255,255,.15);
        }
        .contact-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255,255,255,.06);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .contact-icon svg { width: 15px; height: 15px; color: rgba(255,255,255,.5); }
        .contact-label { font-size: .72rem; color: var(--muted); }
        .contact-value { font-size: .875rem; font-weight: 500; }

        /* Error code */
        .error-code {
            font-family: 'DM Mono', monospace;
            font-size: .72rem;
            color: var(--muted);
            margin-top: 2rem;
            letter-spacing: .05em;
        }

        /* Reason chip */
        .reason-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-family: 'DM Mono', monospace;
            font-size: .72rem;
            color: rgba(255,77,77,.6);
            background: rgba(255,77,77,.07);
            padding: .25rem .65rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="icon-wrap">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286z"/>
        </svg>
    </div>

    <div class="badge">License Expired</div>

    <h1>This application is currently unavailable</h1>
    <div class="app-name">{{ $app_name ?? 'Application' }}</div>

    @if(isset($reason) && $reason !== 'EXPIRED')
        <div class="reason-chip">
            <span>⚠</span> {{ str_replace('_', ' ', $reason) }}
        </div>
    @endif

    @if(!empty($emails) || !empty($phone))
    <div class="card">
        <div class="card-label">Contact Support to Renew</div>
        <div class="contacts">
            @foreach($emails as $email)
            <a href="mailto:{{ $email }}?subject=License Renewal Request — {{ $app_name ?? 'Application' }}" class="contact-item">
                <div class="contact-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="contact-label">Email Support</div>
                    <div class="contact-value">{{ $email }}</div>
                </div>
            </a>
            @endforeach

            @if(!empty($phone))
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="contact-item">
                <div class="contact-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div>
                    <div class="contact-label">Phone Support</div>
                    <div class="contact-value">{{ $phone }}</div>
                </div>
            </a>
            @endif
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-label">License Renewal Required</div>
        <p style="font-size: .875rem; color: var(--muted); line-height: 1.6;">
            Your application license has expired. Please contact your software provider to renew your license and restore access.
        </p>
    </div>
    @endif

    <div class="error-code">
        HTTP 402 · {{ $app_name ?? 'APP' }} · {{ now()->format('Y') }}
    </div>

</div>

</body>
</html>
