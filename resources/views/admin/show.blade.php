@extends('admin.layout')
@section('title', $license->client_name)

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">{{ $license->client_name }}</div>
        <div class="page-sub mono">{{ $license->license_key }}</div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('licenses.index') }}" class="btn btn-ghost btn-sm">← Back</a>
        <a href="{{ route('licenses.edit', $license) }}" class="btn btn-ghost btn-sm">Edit</a>
    </div>
</div>

@php
    $status  = $license->isExpired() && ! in_array($license->status, ['revoked','suspended']) ? 'expired' : $license->status;
    $days    = $license->daysUntilExpiry();
@endphp

<div class="grid-2">

    {{-- LEFT COLUMN --}}
    <div>

        {{-- License Info --}}
        <div class="card">
            <div class="card-head">License Details</div>
            <table class="detail-table">
                <tr>
                    <td>License Key</td>
                    <td><span class="mono" style="color:var(--accent); letter-spacing:.05em;">{{ $license->license_key }}</span></td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td><span class="badge badge-{{ $status }}">{{ ucfirst($status) }}</span></td>
                </tr>
                <tr>
                    <td>Client</td>
                    <td>
                        <div class="fw-600">{{ $license->client_name }}</div>
                        <div class="text-xs text-muted">{{ $license->client_email }}</div>
                    </td>
                </tr>
                <tr>
                    <td>Bound Domain</td>
                    <td><span class="mono">{{ $license->domain }}</span></td>
                </tr>
                <tr>
                    <td>Product</td>
                    <td>{{ $license->product_name }}</td>
                </tr>
                <tr>
                    <td>Expires</td>
                    <td>
                        <span class="{{ $license->isExpired() ? 'text-red' : ($days <= 7 ? 'text-yellow' : 'text-green') }} fw-600">
                            {{ $license->expires_at->format('d M Y') }}
                        </span>
                        @if(! $license->isExpired())
                            <span class="text-xs text-muted"> — {{ $days }} days left</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Grace Period</td>
                    <td>{{ $license->grace_period_days }} days</td>
                </tr>
                <tr>
                    <td>Activated</td>
                    <td class="text-sm">{{ $license->activated_at?->format('d M Y, H:i') ?? '—' }}</td>
                </tr>
                @if($license->features)
                <tr>
                    <td>Features</td>
                    <td><span class="mono text-xs" style="color:var(--text);">{{ json_encode($license->features) }}</span></td>
                </tr>
                @endif
                @if($license->notes)
                <tr>
                    <td>Notes</td>
                    <td class="text-sm">{{ $license->notes }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Actions Card --}}
        <div class="card">
            <div class="card-head">Actions</div>

            <div class="flex gap-2 flex-wrap mb-4">

                {{-- Generate Token --}}
                <form method="POST" action="{{ route('licenses.token', $license) }}">
                    @csrf
                    <button class="btn btn-primary btn-sm">⚡ Generate Token</button>
                </form>

                {{-- Activate --}}
                @if($license->status !== 'active')
                <form method="POST" action="{{ route('licenses.activate', $license) }}">
                    @csrf
                    <button class="btn btn-success btn-sm">✓ Activate</button>
                </form>
                @endif

                {{-- Suspend --}}
                @if($license->status === 'active')
                <form method="POST" action="{{ route('licenses.suspend', $license) }}"
                      onsubmit="return confirm('Suspend this license? Client app will show unavailable page.')">
                    @csrf
                    <button class="btn btn-warning btn-sm">⏸ Suspend</button>
                </form>
                @endif

                {{-- Revoke --}}
                @if($license->status !== 'revoked')
                <form method="POST" action="{{ route('licenses.revoke', $license) }}"
                      onsubmit="return confirm('Revoke this license permanently? This cannot be undone.')">
                    @csrf
                    <button class="btn btn-danger btn-sm">✗ Revoke</button>
                </form>
                @endif

                {{-- Delete --}}
                <form method="POST" action="{{ route('licenses.destroy', $license) }}"
                      onsubmit="return confirm('DELETE this license completely from database? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm" style="background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.2); color:#f87171;">
                        🗑 Delete
                    </button>
                </form>

            </div>

            {{-- Renew Section --}}
            <div style="border-top: 1px solid var(--border); padding-top: 1rem;">
                <div class="text-xs text-muted mb-3">Renew License</div>
                <form method="POST" action="{{ route('licenses.renew', $license) }}">
                    @csrf
                    <div class="flex gap-2 items-center flex-wrap">
                        <div class="form-group" style="margin:0; flex:1; min-width:160px;">
                            <input type="date" name="expires_at"
                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                   value="{{ now()->addYear()->format('Y-m-d') }}">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">↻ Renew & Generate Token</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN --}}
    <div>

        {{-- Event History --}}
        <div class="card" style="max-height: 600px; overflow-y: auto;">
            <div class="card-head">Event History</div>

            @forelse($events as $event)
            <div class="event-item">
                <div class="event-dot" style="background:
                    {{ match($event->event_type) {
                        'revoked'   => 'var(--red)',
                        'suspended' => 'var(--yellow)',
                        'renewed'   => 'var(--green)',
                        'issued'    => 'var(--accent)',
                        default     => 'var(--muted)'
                    } }};"></div>
                <div style="flex:1; min-width:0;">
                    <div class="text-sm fw-600">
                        {{ ucwords(str_replace('_', ' ', $event->event_type)) }}
                    </div>
                    <div class="text-xs text-muted">{{ $event->created_at->format('d M Y, H:i:s') }}</div>
                    @if($event->ip_address)
                        <div class="text-xs mono" style="margin-top:.2rem;">{{ $event->ip_address }}</div>
                    @endif
                    @if($event->payload)
                        <div style="margin-top:.4rem; background:var(--bg); border:1px solid var(--border); border-radius:6px; padding:.5rem .75rem; font-family:var(--mono); font-size:.68rem; color:var(--muted);">
                            {{ json_encode($event->payload) }}
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state" style="padding:1.5rem;">
                <div class="empty-state-sub">No events yet.</div>
            </div>
            @endforelse
        </div>

    </div>
</div>

@endsection
