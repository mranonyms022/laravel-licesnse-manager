@extends('admin.layout')
@section('title', 'All Licenses')

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">All Licenses</div>
        <div class="page-sub">Manage and track all client licenses</div>
    </div>
    <a href="{{ route('licenses.create') }}" class="btn btn-primary">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New License
    </a>
</div>

{{-- Stats --}}
<div class="stat-grid">
    <div class="stat-card blue">
        <div class="stat-label">Total Licenses</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-sub">All time</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Active</div>
        <div class="stat-value">{{ $stats['active'] }}</div>
        <div class="stat-sub">Currently valid</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Expired</div>
        <div class="stat-value">{{ $stats['expired'] }}</div>
        <div class="stat-sub">Need renewal</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-label">Expiring Soon</div>
        <div class="stat-value">{{ $stats['expiring'] }}</div>
        <div class="stat-sub">Within 7 days</div>
    </div>
</div>

{{-- Table Card --}}
<div class="card" style="padding: 0; overflow: hidden;">

    {{-- Search Bar --}}
    <form method="GET" action="{{ route('licenses.index') }}" class="search-bar">
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="q" placeholder="Domain, name, key, email…" value="{{ request('q') }}">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="">All statuses</option>
                @foreach(['active','expired','suspended','revoked','pending'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-ghost">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Filter
            </button>
        </div>
        @if(request('q') || request('status'))
        <div class="form-group">
            <label>&nbsp;</label>
            <a href="{{ route('licenses.index') }}" class="btn btn-ghost">Clear</a>
        </div>
        @endif
    </form>

    {{-- Table --}}
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
                @php
                    $status = $license->isExpired() && $license->status !== 'revoked' && $license->status !== 'suspended'
                              ? 'expired' : $license->status;
                    $days   = $license->daysUntilExpiry();
                @endphp
                <tr>
                    <td>
                        <span class="mono" style="color: var(--accent); letter-spacing: .04em;">
                            {{ $license->license_key }}
                        </span>
                    </td>
                    <td>
                        <div class="fw-600" style="color: var(--text);">{{ $license->client_name }}</div>
                        <div class="text-xs text-muted">{{ $license->client_email }}</div>
                    </td>
                    <td>
                        <span class="mono text-xs">{{ $license->domain }}</span>
                    </td>
                    <td class="text-sm">{{ $license->product_name }}</td>
                    <td>
                        <span class="badge badge-{{ $status }}">{{ ucfirst($status) }}</span>
                    </td>
                    <td>
                        <div class="{{ $license->isExpired() ? 'text-red' : ($days <= 7 ? 'expiry-warn' : 'text-green') }} text-sm fw-600">
                            {{ $license->expires_at->format('d M Y') }}
                        </div>
                        @if(! $license->isExpired())
                            <div class="text-xs text-muted">{{ $days }} days left</div>
                        @else
                            <div class="text-xs text-red">Expired</div>
                        @endif
                    </td>
                    <td class="actions-cell">
                        <div class="flex gap-2 items-center">
                            <a href="{{ route('licenses.show', $license) }}" class="btn btn-ghost btn-sm">View</a>

                            {{-- Quick Token Generate --}}
                            <form method="POST" action="{{ route('licenses.token', $license) }}">
                                @csrf
                                <button class="btn btn-primary btn-sm" title="Generate Token">⚡</button>
                            </form>

                            {{-- Quick Revoke --}}
                            @if($license->status !== 'revoked')
                            <form method="POST" action="{{ route('licenses.revoke', $license) }}"
                                  onsubmit="return confirm('Revoke license for {{ addslashes($license->client_name) }}?')">
                                @csrf
                                <button class="btn btn-danger btn-sm" title="Revoke">✗</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <div class="empty-state-title">No licenses found</div>
                            <div class="empty-state-sub">
                                @if(request('q') || request('status'))
                                    Try different search terms or <a href="{{ route('licenses.index') }}" style="color:var(--accent)">clear filters</a>
                                @else
                                    <a href="{{ route('licenses.create') }}" style="color:var(--accent)">Create your first license →</a>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($licenses->hasPages())
        <div class="pagination">
            {{ $licenses->onFirstPage() ? '' : '' }}
            @foreach($licenses->links()->elements[0] ?? [] as $page => $url)
                @if($page == $licenses->currentPage())
                    <span class="active"><span>{{ $page }}</span></span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        </div>
    @endif

</div>

@endsection
