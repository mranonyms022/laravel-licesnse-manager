@extends('admin.layout')
@section('title', 'Edit — ' . $license->license_key)

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">Edit License</div>
        <div class="page-sub mono">{{ $license->license_key }}</div>
    </div>
    <a href="{{ route('licenses.show', $license) }}" class="btn btn-ghost btn-sm">← Back</a>
</div>

<div style="max-width: 680px;">
    <div class="alert alert-warning">
        ⚠ &nbsp;Changing domain requires regenerating the token. Old token will stop working on new domain.
    </div>

    <div class="card">
        <div class="card-head">Edit Details</div>

        <form method="POST" action="{{ route('licenses.update', $license) }}">
            @csrf
            @method('PUT')

            <div class="grid-2">
                <div class="form-group">
                    <label>Client Name</label>
                    <input type="text" name="client_name"
                           value="{{ old('client_name', $license->client_name) }}" required>
                    @error('client_name')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Client Email</label>
                    <input type="email" name="client_email"
                           value="{{ old('client_email', $license->client_email) }}" required>
                    @error('client_email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Bound Domain</label>
                <input type="text" name="domain"
                       value="{{ old('domain', $license->domain) }}" required>
                @error('domain')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Grace Period (days)</label>
                <input type="number" name="grace_period_days"
                       value="{{ old('grace_period_days', $license->grace_period_days) }}"
                       min="0" max="30" required>
            </div>

            <div class="form-group">
                <label>Internal Notes</label>
                <textarea name="notes">{{ old('notes', $license->notes) }}</textarea>
            </div>

            <hr class="divider">

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('licenses.show', $license) }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
