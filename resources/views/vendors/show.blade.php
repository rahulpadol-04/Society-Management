@extends('layouts.app')
@section('title', $vendor->name)

@section('page-actions')
    @can('update', $vendor)
        <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    @endcan
    @can('delete', $vendor)
        <form method="POST" action="{{ route('vendors.destroy', $vendor) }}" class="d-inline" data-confirm="Delete this vendor?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    {{-- Profile card --}}
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h2 class="h5 mb-0">{{ $vendor->name }}</h2>
                    @if($vendor->company)<div class="text-muted small">{{ $vendor->company }}</div>@endif
                </div>
                <span class="badge text-bg-{{ $vendor->status === 'active' ? 'success' : ($vendor->status === 'blacklisted' ? 'danger' : 'warning') }} text-capitalize">
                    {{ ucfirst($vendor->status) }}
                </span>
            </div>

            <div class="mb-2">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="bi bi-star{{ $i <= round($vendor->rating) ? '-fill text-warning' : ' text-muted' }}"></i>
                @endfor
                <span class="small text-muted ms-1">{{ number_format($vendor->rating, 1) }} / 5 ({{ $vendor->ratings_count }} ratings)</span>
            </div>

            <span class="badge text-bg-secondary text-capitalize mb-3">{{ str_replace('_', ' ', $vendor->category) }}</span>

            <ul class="list-unstyled small text-muted mb-0">
                @if($vendor->contact_person)<li><i class="bi bi-person me-1"></i>{{ $vendor->contact_person }}</li>@endif
                @if($vendor->phone)<li><i class="bi bi-telephone me-1"></i>{{ $vendor->phone }}</li>@endif
                @if($vendor->email)<li><i class="bi bi-envelope me-1"></i>{{ $vendor->email }}</li>@endif
                @if($vendor->gstin)<li><i class="bi bi-card-text me-1"></i>GSTIN: {{ $vendor->gstin }}</li>@endif
                @if($vendor->address)<li class="mt-1"><i class="bi bi-geo-alt me-1"></i>{{ $vendor->address }}</li>@endif
            </ul>
            @if($vendor->notes)
                <hr class="my-2"><p class="small text-muted mb-0">{{ $vendor->notes }}</p>
            @endif
        </div></div>
    </div>

    {{-- Tabs --}}
    <div class="col-lg-8">
        <ul class="nav nav-tabs mb-3" id="vendorTabs">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-contracts">Contracts <span class="badge text-bg-secondary">{{ $vendor->contracts->count() }}</span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-workorders">Work Orders <span class="badge text-bg-secondary">{{ $vendor->workOrders->count() }}</span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-payments">Payments <span class="badge text-bg-secondary">{{ $vendor->payments->count() }}</span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-ratings">Ratings <span class="badge text-bg-secondary">{{ $vendor->ratings->count() }}</span></a></li>
        </ul>

        <div class="tab-content">

            {{-- Contracts tab --}}
            <div class="tab-pane fade show active" id="tab-contracts">
                @can('create', App\Models\Vendor::class)
                <button class="btn btn-sm btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#addContractModal">
                    <i class="bi bi-plus-lg"></i> Add Contract
                </button>
                @endcan
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Title</th><th>No.</th><th>Period</th><th>Value</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @forelse ($vendor->contracts as $contract)
                            <tr>
                                <td>{{ $contract->title }}</td>
                                <td class="text-muted small">{{ $contract->contract_number ?? '—' }}</td>
                                <td class="small">
                                    {{ $contract->start_date?->format('d M Y') ?? '—' }}
                                    @if($contract->end_date) – {{ $contract->end_date->format('d M Y') }}@endif
                                </td>
                                <td>{{ money($contract->value) }}</td>
                                <td><span class="badge text-bg-light text-capitalize">{{ $contract->status }}</span></td>
                                <td class="text-end">
                                    @can('delete', $vendor)
                                    <form method="POST" action="{{ route('vendors.contracts.destroy', [$vendor, $contract]) }}" data-confirm="Delete contract?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No contracts.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Work Orders tab --}}
            <div class="tab-pane fade" id="tab-workorders">
                @can('create', App\Models\WorkOrder::class)
                <button class="btn btn-sm btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#addWorkOrderModal">
                    <i class="bi bi-plus-lg"></i> New Work Order
                </button>
                @endcan
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Reference</th><th>Title</th><th>Priority</th><th>Status</th><th>Amount</th><th>Scheduled</th></tr></thead>
                        <tbody>
                        @forelse ($vendor->workOrders as $wo)
                            <tr>
                                <td><a href="{{ route('work-orders.show', $wo) }}" class="fw-semibold">{{ $wo->reference }}</a></td>
                                <td>{{ \Illuminate\Support\Str::limit($wo->title, 35) }}</td>
                                <td><span class="badge text-bg-light text-capitalize">{{ $wo->priority }}</span></td>
                                <td><span class="badge text-bg-{{ $wo->status === 'completed' ? 'success' : ($wo->status === 'cancelled' ? 'secondary' : 'info') }} text-capitalize">{{ str_replace('_', ' ', $wo->status) }}</span></td>
                                <td>{{ money($wo->amount) }}</td>
                                <td class="small text-muted">{{ $wo->scheduled_for?->format('d M Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No work orders.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Payments tab --}}
            <div class="tab-pane fade" id="tab-payments">
                @can('pay', $vendor)
                <button class="btn btn-sm btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                    <i class="bi bi-plus-lg"></i> Record Payment
                </button>
                @endcan
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Amount</th><th>Method</th><th>Reference</th><th>Work Order</th><th>Paid At</th></tr></thead>
                        <tbody>
                        @forelse ($vendor->payments as $payment)
                            <tr>
                                <td class="fw-semibold">{{ money($payment->amount) }}</td>
                                <td class="text-capitalize small">{{ str_replace('_', ' ', $payment->method) }}</td>
                                <td class="text-muted small">{{ $payment->reference ?? '—' }}</td>
                                <td class="text-muted small">{{ $payment->workOrder?->reference ?? '—' }}</td>
                                <td class="text-muted small">{{ $payment->paid_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No payments recorded.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ratings tab --}}
            <div class="tab-pane fade" id="tab-ratings">
                @can('rate', $vendor)
                <button class="btn btn-sm btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#addRatingModal">
                    <i class="bi bi-star"></i> Add Rating
                </button>
                @endcan
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
                        <tbody>
                        @forelse ($vendor->ratings as $vendorRating)
                            <tr>
                                <td>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $vendorRating->rating ? '-fill text-warning' : ' text-muted' }}" style="font-size:.8rem"></i>
                                    @endfor
                                </td>
                                <td class="small">{{ $vendorRating->comment ?? '—' }}</td>
                                <td class="text-muted small">{{ $vendorRating->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No ratings yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /.tab-content --}}
    </div>
</div>

{{-- Add Contract Modal --}}
<div class="modal fade" id="addContractModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('vendors.contracts.store', $vendor) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Contract</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contract No.</label>
                            <input type="text" name="contract_number" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach (['active', 'draft', 'expired', 'terminated'] as $cs)
                                    <option value="{{ $cs }}">{{ ucfirst($cs) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Value (₹)</label>
                        <input type="number" name="value" step="0.01" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Terms</label>
                        <textarea name="terms" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Contract</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Add Work Order Modal --}}
<div class="modal fade" id="addWorkOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('vendors.workorders.store', $vendor) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">New Work Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                @foreach (['low', 'medium', 'high', 'critical'] as $p)
                                    <option value="{{ $p }}" @selected($p === 'medium')>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Scheduled For</label>
                            <input type="date" name="scheduled_for" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estimated Amount (₹)</label>
                        <input type="number" name="amount" step="0.01" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Record Payment Modal --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('vendors.pay', $vendor) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Record Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Method</label>
                            <select name="method" class="form-select">
                                @foreach (['bank_transfer', 'cash', 'cheque', 'online', 'upi'] as $m)
                                    <option value="{{ $m }}">{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Paid At</label>
                            <input type="datetime-local" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference No.</label>
                        <input type="text" name="reference" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Work Order (optional)</label>
                        <select name="work_order_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($vendor->workOrders as $wo)
                                <option value="{{ $wo->id }}">{{ $wo->reference }} — {{ \Illuminate\Support\Str::limit($wo->title, 30) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Add Rating Modal --}}
<div class="modal fade" id="addRatingModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('vendors.rate', $vendor) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Rate Vendor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <select name="rating" class="form-select">
                            @for ($r = 5; $r >= 1; $r--)
                                <option value="{{ $r }}">{{ $r }} ★</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comment (optional)</label>
                        <textarea name="comment" rows="3" class="form-control" placeholder="Share your experience..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Rating</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
