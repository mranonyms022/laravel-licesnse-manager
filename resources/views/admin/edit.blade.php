@extends('admin.layout')
@section('title', 'Edit — ' . $license->license_key)

@section('content')

<div style="max-width: 640px;">
    <div class="flex items-center gap-3" style="margin-bottom: 1.5rem;">
        <a href="{{ route('licenses.show', $license) }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>

    <div class="card">
        <div class="card-title">Edit License</div>

        <div class="alert alert-warning" style="margin-bottom: 1.25rem;">
            ⚠ Changing the domain here does NOT automatically regenerate the token. You must regenerate the token after editing for the change to take effect on the client.
        </div>

        <form method="POST" action="{{ route('licenses.update', $license) }}">
            @csrf
            @method('PUT')

            <div class="grid-2">
                <div class="form-group">
                    <label>Client Name</label>
                    <input type="text" name="client_name" value="{{ old('client_name', $license->client_name) }}" required>
                    @error('client_name')<div class="text-xs text-red" style="margin-top:.3rem;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Client Email</label>
                    <input type="email" name="client_email" value="{{ old('client_email', $license->client_email) }}" required>
                    @error('client_email')<div class="text-xs text-red" style="margin-top:.3rem;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label>Bound Domain</label>
                <input type="text" name="domain" value="{{ old('domain', $license->domain) }}" required>
                @error('domain')<div class="text-xs text-red" style="margin-top:.3rem;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Grace Period (days)</label>
                <input type="text" name="grace_period_days" value="{{ old('grace_period_days', $license->grace_period_days) }}" required>
            </div>

            <div class="form-group">
                <label>Notes</label>
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
