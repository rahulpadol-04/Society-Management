@extends('layouts.app')
@section('title', 'Work Order '.$workOrder->reference)

@section('page-actions')
    @can('update', $workOrder)
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statusModal">
        <i class="bi bi-arrow-repeat"></i> Update Status
    </button>
    @endcan
    <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary ms-1"><i class="bi bi-arrow-left"></i> All Work Orders</a>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h2 class="h5">{{ $workOrder->title }}</h2>
                <span class="badge text-bg-{{ $workOrder->status === 'completed' ? 'success' : ($workOrder->status === 'cancelled' ? 'secondary' : 'info') }} text-capitalize">
                    {{ str_replace('_', ' ', $workOrder->status) }}
                </span>
            </div>
            <p class="text-muted">{{ $workOrder->description ?: 'No description provided.' }}</p>
            <div class="row small text-muted">
                <div class="col-sm-4"><strong>Reference:</strong> {{ $workOrder->reference }}</div>
                <div class="col-sm-4"><strong>Priority:</strong> {{ ucfirst($workOrder->priority) }}</div>
                <div class="col-sm-4"><strong>Amount:</strong> {{ money($workOrder->amount) }}</div>
                <div class="col-sm-4 mt-1"><strong>Vendor:</strong> {{ $workOrder->vendor?->name ?? '—' }}</div>
                <div class="col-sm-4 mt-1"><strong>Scheduled:</strong> {{ $workOrder->scheduled_for?->format('d M Y') ?? '—' }}</div>
                <div class="col-sm-4 mt-1"><strong>Completed:</strong> {{ $workOrder->completed_at?->format('d M Y H:i') ?? '—' }}</div>
                <div class="col-sm-4 mt-1"><strong>Created by:</strong> {{ $workOrder->creator?->name ?? '—' }}</div>
                <div class="col-sm-4 mt-1"><strong>Created:</strong> {{ $workOrder->created_at->format('d M Y') }}</div>
            </div>
        </div></div>
    </div>
    <div class="col-lg-4">
        @if($workOrder->vendor)
        <div class="card shadow-sm"><div class="card-body">
            <h3 class="h6">Vendor</h3>
            <a href="{{ route('vendors.show', $workOrder->vendor) }}" class="fw-semibold text-decoration-none">{{ $workOrder->vendor->name }}</a>
            @if($workOrder->vendor->company)<div class="text-muted small">{{ $workOrder->vendor->company }}</div>@endif
            @if($workOrder->vendor->phone)<div class="small"><i class="bi bi-telephone me-1"></i>{{ $workOrder->vendor->phone }}</div>@endif
        </div></div>
        @endif
    </div>
</div>

{{-- Status Modal --}}
@can('update', $workOrder)
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="POST" action="{{ route('work-orders.status', $workOrder) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h6 class="modal-title">Update Status</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <select name="status" class="form-select">
                        @foreach (['open', 'assigned', 'in_progress', 'completed', 'cancelled'] as $st)
                            <option value="{{ $st }}" @selected($workOrder->status === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer py-2">
                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
