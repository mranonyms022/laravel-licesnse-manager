@extends('admin.layout')
@section('title', $license->client_name . ' — ' . $license->license_key)

@section('content')

<div class="flex items-center gap-3 mb-4" style="margin-bottom: 1.5rem;">
    <a href="{{ route('licenses.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    <a href="{{ route('licenses.edit', $license) }}" class="btn btn-ghost btn-sm">Edit</a>
</div>

<div class="grid-2">
    {{-- Left: Details --}}
    <div>
        <div class="card">
            <div class="card-title">License Details</div>

            <div style="display: grid; gap: 1rem;">
                <div>
                    <div class="text-xs text-muted">License Key</div>
                    <div class="mono" style="font-size: 1rem; color: var(--accent); margin-top: .2rem; letter-spacing: .06em;">{{ $license->license_key }}</div>
                </div>
                <div>
                    <div class="text-xs text-muted">Status</div>
                    @php $s = $license->isExpired() ? 'expired' : $license->status; @endphp
                    <div style="margin-top:.25rem;"><span class="badge badge-{{ $s }}">{{ ucfirst($s) }}</span></div>
                </div>
                <div>
                    <div class="text-xs text-muted">Client</div>
                    <div style="font-weight:500; margin-top:.2rem;">{{ $license->client_name }}</div>
                    <div class="text-xs text-muted">{{ $license->client_email }}</div>
                </div>
                <div>
                    <div class="text-xs text-muted">Domain (Bound)</div>
                    <div class="mono text-sm" style="margin-top:.2rem;">{{ $license->domain }}</div>
                </div>
                <div>
                    <div class="text-xs text-muted">Product</div>
                    <div style="margin-top:.2rem;">{{ $license->product_name }}</div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <div class="text-xs text-muted">Expires</div>
                        <div class="text-sm {{ $license->isExpired() ? 'text-red' : ($license->daysUntilExpiry() <= 7 ? 'text-yellow' : 'text-green') }}" style="margin-top:.2rem;">
                            {{ $license->expires_at->format('d M Y') }}
                        </div>
                        @if(! $license->isExpired())
                            <div class="text-xs text-muted">{{ $license->daysUntilExpiry() }} days remaining</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-muted">Grace Period</div>
                        <div class="text-sm" style="margin-top:.2rem;">{{ $license->grace_period_days }} days</div>
                    </div>
                </div>
                @if($license->features)
                <div>
                    <div class="text-xs text-muted">Features</div>
                    <div class="token-box" style="margin-top:.3rem; font-size:.75rem;">{{ json_encode($license->features, JSON_PRETTY_PRINT) }}</div>
                </div>
                @endif
                @if($license->notes)
                <div>
                    <div class="text-xs text-muted">Notes</div>
                    <div class="text-sm" style="margin-top:.2rem;">{{ $license->notes }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="card">
            <div class="card-title">Actions</div>
            <div class="flex gap-2 flex-wrap">
                {{-- Generate Token --}}
                <form method="POST" action="{{ route('licenses.token', $license) }}">
                    @csrf
                    <button class="btn btn-primary btn-sm">⚡ Generate Token</button>
                </form>

                {{-- Renew --}}
                <button class="btn btn-success btn-sm" onclick="document.getElementById('renew-form').style.display='block'">↻ Renew</button>

                {{-- Activate --}}
                @if($license->status !== 'active')
                <form method="POST" action="{{ route('licenses.activate', $license) }}">
                    @csrf
                    <button class="btn btn-ghost btn-sm">✓ Activate</button>
                </form>
                @endif

                {{-- Suspend --}}
                @if($license->status === 'active')
                <form method="POST" action="{{ route('licenses.suspend', $license) }}">
                    @csrf
                    <button class="btn btn-warning btn-sm" onclick="return confirm('Suspend this license?')">⏸ Suspend</button>
                </form>
                @endif

                {{-- Revoke --}}
                @if($license->status !== 'revoked')
                <form method="POST" action="{{ route('licenses.revoke', $license) }}">
                    @csrf
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Revoke this license? This cannot be undone.')">✗ Revoke</button>
                </form>
                @endif
            </div>

            {{-- Renew form (hidden) --}}
            <div id="renew-form" style="display:none; margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border);">
                <form method="POST" action="{{ route('licenses.renew', $license) }}">
                    @csrf
                    <div class="form-inline">
                        <div class="form-group">
                            <label>New Expiry Date</label>
                            <input type="date" name="expires_at" min="{{ now()->addDay()->format('Y-m-d') }}"
                                   value="{{ now()->addYear()->format('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-success">Renew & Generate Token</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: Events --}}
    <div>
        <div class="card">
            <div class="card-title">Event History</div>
            @forelse($events as $event)
            <div style="display:flex; align-items:flex-start; gap:.75rem; padding: .75rem 0; border-bottom: 1px solid var(--border);">
                <div style="width: 8px; height: 8px; border-radius:50%; background: var(--accent); margin-top: .35rem; flex-shrink:0;"></div>
                <div>
                    <div class="text-sm" style="font-weight:500;">{{ str_replace('_', ' ', ucfirst($event->event_type)) }}</div>
                    <div class="text-xs text-muted">{{ $event->created_at->format('d M Y, H:i:s') }}</div>
                    @if($event->ip_address)
                        <div class="text-xs text-muted mono">{{ $event->ip_address }}</div>
                    @endif
                    @if($event->payload)
                        <div class="token-box" style="margin-top:.4rem; font-size:.7rem;">{{ json_encode($event->payload) }}</div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-sm text-muted" style="text-align:center; padding: 1.5rem 0;">No events yet.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection
