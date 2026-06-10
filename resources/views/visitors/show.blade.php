@extends('layouts.app')
@section('title', 'Pass '.$pass->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('visitors.index') }}">Visitors</a></li>
    <li class="breadcrumb-item active">{{ $pass->code }}</li>
@endsection

@section('page-actions')
    @can('approve', $pass)
        @if ($pass->status === 'pending')
            <form method="POST" action="{{ route('visitors.approve', $pass) }}" class="d-inline">
                @csrf
                <button class="btn btn-success"><i class="bi bi-check-circle"></i> Approve</button>
            </form>
            <button class="btn btn-outline-danger ms-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="bi bi-x-circle"></i> Reject
            </button>
        @endif
    @endcan
@endsection

@section('content')
<div class="row g-3">

    {{-- Pass Detail --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h2 class="h5 mb-1">{{ $pass->name }}</h2>
                    <span class="font-monospace small text-muted">{{ $pass->code }}</span>
                </div>
                <span class="badge fs-6 text-capitalize
                    @if($pass->status === 'approved') text-bg-success
                    @elseif($pass->status === 'pending') text-bg-warning
                    @elseif($pass->status === 'rejected') text-bg-danger
                    @elseif($pass->status === 'used') text-bg-secondary
                    @else text-bg-light @endif
                ">{{ $pass->status }}</span>
            </div>

            <div class="row small text-muted g-2">
                <div class="col-sm-4"><strong>Type:</strong> {{ ucfirst($pass->type) }}</div>
                <div class="col-sm-4"><strong>Phone:</strong> {{ $pass->phone ?? '—' }}</div>
                <div class="col-sm-4"><strong>Vehicle:</strong> {{ $pass->vehicle_number ?? '—' }}</div>
                <div class="col-sm-4"><strong>Host:</strong> {{ $pass->host?->name ?? '—' }}</div>
                <div class="col-sm-4"><strong>Flat:</strong> {{ $pass->flat?->number ?? '—' }}</div>
                <div class="col-sm-4"><strong>Purpose:</strong> {{ $pass->purpose ?? '—' }}</div>
                <div class="col-sm-4"><strong>Expected:</strong> {{ $pass->expected_at?->format('d M Y H:i') ?? '—' }}</div>
                <div class="col-sm-4"><strong>Valid Until:</strong> {{ $pass->valid_until?->format('d M Y H:i') ?? '—' }}</div>
                <div class="col-sm-4"><strong>Entries:</strong> {{ $pass->entries_used }} / {{ $pass->max_entries }}</div>
                @if ($pass->approved_at)
                    <div class="col-sm-4"><strong>Approved By:</strong> {{ $pass->approver?->name ?? '—' }}</div>
                    <div class="col-sm-4"><strong>Approved At:</strong> {{ $pass->approved_at->format('d M Y H:i') }}</div>
                @endif
            </div>
        </div></div>

        {{-- Entry History --}}
        <div class="card shadow-sm"><div class="card-body">
            <h3 class="h6 mb-3"><i class="bi bi-journal-check"></i> Entry History</h3>
            @if ($pass->logs->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm table-hover datatable">
                        <thead>
                            <tr><th>Checked In</th><th>Checked Out</th><th>Gate</th><th>Guard</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($pass->logs as $log)
                                <tr>
                                    <td class="small">{{ $log->checked_in_at->format('d M Y H:i') }}</td>
                                    <td class="small">{{ $log->checked_out_at?->format('d M Y H:i') ?? '—' }}</td>
                                    <td class="small">{{ $log->gate ?? '—' }}</td>
                                    <td class="small">{{ $log->guardUser?->name ?? '—' }}</td>
                                    <td>
                                        @if ($log->status === 'in')
                                            <span class="badge text-bg-success">Inside</span>
                                        @else
                                            <span class="badge text-bg-secondary">Out</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small">No gate entries recorded yet.</p>
            @endif
        </div></div>
    </div>

    {{-- QR Code --}}
    <div class="col-lg-4">
        <div class="card shadow-sm text-center"><div class="card-body">
            <h3 class="h6 mb-3"><i class="bi bi-qr-code"></i> Visitor QR Code</h3>
            <img src="{{ $qrUrl }}" alt="QR Code for {{ $pass->code }}" class="img-fluid mb-2" style="max-width:180px;">
            <div class="font-monospace small text-muted mt-1">{{ $pass->code }}</div>
            <p class="text-muted small mt-2">Share this QR code or code with your visitor for gate entry.</p>
            @if ($pass->isUsable())
                <span class="badge text-bg-success"><i class="bi bi-check-circle"></i> Valid &amp; Usable</span>
            @else
                <span class="badge text-bg-secondary"><i class="bi bi-lock"></i> Not usable</span>
            @endif
        </div></div>
    </div>

</div>

{{-- Reject Modal --}}
@can('approve', $pass)
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="rejectModalLabel">Reject Visitor Pass</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="{{ route('visitors.reject', $pass) }}">
            @csrf
            <div class="modal-body">
                <label class="form-label">Reason (optional)</label>
                <textarea name="reason" rows="3" class="form-control" placeholder="State the reason for rejection…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject Pass</button>
            </div>
        </form>
    </div></div>
</div>
@endcan
@endsection
