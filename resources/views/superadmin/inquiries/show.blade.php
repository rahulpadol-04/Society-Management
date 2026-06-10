@extends('layouts.app')
@section('title', 'Inquiry: '.($inquiry->subject ?: 'View'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inquiries.index') }}">Inquiries</a></li>
    <li class="breadcrumb-item active">{{ $inquiry->name }}</li>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-md-7">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Message</h2>
            <dl class="row small mb-3">
                <dt class="col-4 text-muted">From</dt>
                <dd class="col-8">{{ $inquiry->name }}</dd>
                <dt class="col-4 text-muted">Email</dt>
                <dd class="col-8">{{ $inquiry->email }}</dd>
                @if ($inquiry->phone)
                    <dt class="col-4 text-muted">Phone</dt>
                    <dd class="col-8">{{ $inquiry->phone }}</dd>
                @endif
                @if ($inquiry->society_name)
                    <dt class="col-4 text-muted">Society</dt>
                    <dd class="col-8">{{ $inquiry->society_name }}</dd>
                @endif
                @if ($inquiry->subject)
                    <dt class="col-4 text-muted">Subject</dt>
                    <dd class="col-8">{{ $inquiry->subject }}</dd>
                @endif
                <dt class="col-4 text-muted">Received</dt>
                <dd class="col-8">{{ $inquiry->created_at->format('d M Y, H:i') }}</dd>
            </dl>
            <div class="border rounded p-3 small bg-body-tertiary">
                {!! nl2br(e($inquiry->message)) !!}
            </div>
        </div></div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Update Status</h2>
            @can('update', $inquiry)
                <form method="POST" action="{{ route('inquiries.status', $inquiry) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach (['new', 'in_progress', 'responded', 'closed'] as $st)
                                <option value="{{ $st }}" {{ $inquiry->status === $st ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $st)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Internal Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes', $inquiry->notes) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            @endcan
        </div></div>
    </div>
</div>
@endsection
