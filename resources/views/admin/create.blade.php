@extends('admin.layout')
@section('title', 'New License')

@section('content')

<div style="max-width: 640px;">
    <div class="flex items-center gap-3" style="margin-bottom: 1.5rem;">
        <a href="{{ route('licenses.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>

    <div class="card">
        <div class="card-title">Create New License</div>

        <form method="POST" action="{{ route('licenses.store') }}">
            @csrf

            <div class="grid-2">
                <div class="form-group">
                    <label>Client Name *</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required placeholder="Acme Corp">
                    @error('client_name')<div class="text-xs text-red" style="margin-top:.3rem;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Client Email *</label>
                    <input type="email" name="client_email" value="{{ old('client_email') }}" required placeholder="admin@acme.com">
                    @error('client_email')<div class="text-xs text-red" style="margin-top:.3rem;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label>Bound Domain * <span class="text-muted">(e.g. app.client.com — no http://)</span></label>
                <input type="text" name="domain" value="{{ old('domain') }}" required placeholder="app.client.com">
                @error('domain')<div class="text-xs text-red" style="margin-top:.3rem;">{{ $message }}</div>@enderror
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="product_name" value="{{ old('product_name', 'my-saas-app') }}" required>
                    @error('product_name')<div class="text-xs text-red" style="margin-top:.3rem;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Grace Period (days)</label>
                    <input type="text" name="grace_period_days" value="{{ old('grace_period_days', 3) }}" required>
                    @error('grace_period_days')<div class="text-xs text-red" style="margin-top:.3rem;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label>Expiry Date *</label>
                <input type="date" name="expires_at" value="{{ old('expires_at', now()->addYear()->format('Y-m-d')) }}" required min="{{ now()->addDay()->format('Y-m-d') }}">
                @error('expires_at')<div class="text-xs text-red" style="margin-top:.3rem;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Features <span class="text-muted">(JSON, optional)</span></label>
                <textarea name="features" placeholder='{"users": 50, "modules": ["crm", "reports"]}'>{{ old('features') }}</textarea>
                @error('features')<div class="text-xs text-red" style="margin-top:.3rem;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Notes <span class="text-muted">(internal only)</span></label>
                <textarea name="notes" placeholder="Internal notes about this license…">{{ old('notes') }}</textarea>
            </div>

            <hr class="divider">

            <div class="alert alert-info">
                ℹ After creating the license, a signed token will be generated automatically. You will copy and send it to the client.
            </div>

            <button type="submit" class="btn btn-primary">Create License & Generate Token →</button>
        </form>
    </div>
</div>

@endsection
