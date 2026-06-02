@extends('admin.layout')
@section('title', 'New License')

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">New License</div>
        <div class="page-sub">Create a license and generate a signed token</div>
    </div>
    <a href="{{ route('licenses.index') }}" class="btn btn-ghost btn-sm">← Back</a>
</div>

<div style="max-width: 680px;">
    <div class="card">
        <div class="card-head">License Details</div>

        <form method="POST" action="{{ route('licenses.store') }}">
            @csrf

            <div class="grid-2">
                <div class="form-group">
                    <label>Client Name *</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}"
                           required placeholder="Acme Corp">
                    @error('client_name')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Client Email *</label>
                    <input type="email" name="client_email" value="{{ old('client_email') }}"
                           required placeholder="admin@acme.com">
                    @error('client_email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Bound Domain * <span class="text-muted">(without http:// — e.g. app.client.com)</span></label>
                <input type="text" name="domain" value="{{ old('domain') }}"
                       required placeholder="app.client.com">
                @error('domain')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="product_name"
                           value="{{ old('product_name', 'my-app') }}" required>
                    @error('product_name')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Grace Period (days)</label>
                    <input type="number" name="grace_period_days"
                           value="{{ old('grace_period_days', 3) }}" min="0" max="30" required>
                </div>
            </div>

            <div class="form-group">
                <label>Expiry Date *</label>
                <input type="date" name="expires_at"
                       value="{{ old('expires_at', now()->addYear()->format('Y-m-d')) }}"
                       min="{{ now()->addDay()->format('Y-m-d') }}" required>
                @error('expires_at')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Features <span class="text-muted">(JSON, optional)</span></label>
                <textarea name="features"
                          placeholder='{"users": 50, "modules": ["crm", "reports"]}'>{{ old('features') }}</textarea>
                @error('features')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Internal Notes</label>
                <textarea name="notes" placeholder="Notes about this client/license…">{{ old('notes') }}</textarea>
            </div>

            <hr class="divider">

            <div class="alert alert-info mb-4">
                ℹ &nbsp;After creating, a signed token will be generated automatically. Copy it and send to the client.
            </div>

            <button type="submit" class="btn btn-primary">
                Create License & Generate Token →
            </button>
        </form>
    </div>
</div>

@endsection
