@extends('admin.layout')
@section('title', 'All Licenses')

@section('content')

{{-- Stats --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Licenses</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Active</div>
        <div class="stat-value">{{ $stats['active'] }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Expired</div>
        <div class="stat-value">{{ $stats['expired'] }}</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-label">Expiring in 7 days</div>
        <div class="stat-value">{{ $stats['expiring'] }}</div>
    </div>
</div>

{{-- Filters + Actions --}}
<div class="card">
    <form method="GET" action="{{ route('licenses.index') }}" class="form-inline">
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="q" placeholder="Domain, name, key…" value="{{ request('q') }}">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" style="width: 140px;">
                <option value="">All statuses</option>
                @foreach(['active','expired','suspended','revoked','pending'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-ghost">Filter</button>
        </div>
        <div style="margin-left: auto;">
            <a href="{{ route('licenses.create') }}" class="btn btn-primary">+ New License</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>License Key</th>
                    <th>Client</th>
                    <th>Domain</th>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($licenses as $license)
                <tr>
                    <td class="mono">{{ $license->license_key }}</td>
                    <td>
                        <div style="font-weight: 500;">{{ $license->client_name }}</div>
                        <div class="text-xs text-muted">{{ $license->client_email }}</div>
                    </td>
                    <td class="mono text-xs">{{ $license->domain }}</td>
                    <td class="text-sm">{{ $license->product_name }}</td>
                    <td>
                        @php
                            $s = $license->isExpired() ? 'expired' : $license->status;
                        @endphp
                        <span class="badge badge-{{ $s }}">{{ ucfirst($s) }}</span>
                    </td>
                    <td class="text-sm">
                        <span class="{{ $license->isExpired() ? 'text-red' : ($license->daysUntilExpiry() <= 7 ? 'text-yellow' : '') }}">
                            {{ $license->expires_at->format('d M Y') }}
                        </span>
                        @if(! $license->isExpired())
                            <div class="text-xs text-muted">{{ $license->daysUntilExpiry() }} days left</div>
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a href="{{ route('licenses.show', $license) }}" class="btn btn-ghost btn-sm">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--muted);">
                        No licenses found. <a href="{{ route('licenses.create') }}" style="color: var(--accent);">Create one →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($licenses->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border);">
            {{ $licenses->links('admin.pagination') }}
        </div>
    @endif
</div>

@endsection
