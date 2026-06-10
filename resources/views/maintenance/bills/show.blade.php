@extends('layouts.app')
@section('title', 'Bill '.$bill->bill_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('maintenance.index') }}">Maintenance Billing</a></li>
    <li class="breadcrumb-item active">{{ $bill->bill_number }}</li>
@endsection

@section('page-actions')
    <a href="{{ route('maintenance.invoice', $bill) }}" class="btn btn-outline-secondary" target="_blank">
        <i class="bi bi-printer"></i> Invoice
    </a>
    @can('waive', $bill)
        @if (!in_array($bill->status, ['paid', 'cancelled']))
            <button type="button" class="btn btn-outline-warning ms-2" data-bs-toggle="modal" data-bs-target="#waiveModal">
                <i class="bi bi-x-circle"></i> Waive
            </button>
        @endif
    @endcan
    @can('collect', $bill)
        @if (!in_array($bill->status, ['paid', 'cancelled']))
            <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="bi bi-cash-coin"></i> Record Payment
            </button>
        @endif
    @endcan
@endsection

@section('content')
<div class="row g-3">
    {{-- Bill Details --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="h5 mb-0">{{ $bill->bill_number }}</h2>
                        <div class="text-muted small">Period: {{ $bill->period }}</div>
                    </div>
                    @php
                        $statusColors = [
                            'unpaid'    => 'warning',
                            'partial'   => 'info',
                            'paid'      => 'success',
                            'overdue'   => 'danger',
                            'cancelled' => 'secondary',
                        ];
                    @endphp
                    <span class="badge text-bg-{{ $statusColors[$bill->status] ?? 'light' }} fs-6 text-capitalize">
                        {{ $bill->status }}
                    </span>
                </div>

                <div class="row small text-muted mb-3">
                    <div class="col-sm-4"><strong>Flat:</strong> {{ $bill->flat?->number ?? 'N/A' }}</div>
                    <div class="col-sm-4"><strong>Resident:</strong> {{ $bill->resident?->name ?? 'N/A' }}</div>
                    <div class="col-sm-4"><strong>Bill Date:</strong> {{ $bill->bill_date?->format('d M Y') }}</div>
                    <div class="col-sm-4 mt-1"><strong>Due Date:</strong>
                        {{ $bill->due_date?->format('d M Y') }}
                        @if ($bill->isOverdue()) <span class="badge text-bg-danger ms-1">Overdue</span> @endif
                    </div>
                    <div class="col-sm-4 mt-1"><strong>Total:</strong> {{ money($bill->total) }}</div>
                    <div class="col-sm-4 mt-1"><strong>Balance:</strong>
                        <span class="{{ $bill->balance > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                            {{ money($bill->balance) }}
                        </span>
                    </div>
                </div>

                {{-- Line Items --}}
                @if ($bill->line_items)
                    <h3 class="h6 mb-2">Line Items</h3>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Head</th><th>Type</th><th class="text-end">Amount</th><th class="text-end">Tax</th><th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($bill->line_items as $item)
                                <tr>
                                    <td>{{ $item['head'] ?? '—' }}</td>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $item['type'] ?? '') }}</td>
                                    <td class="text-end">{{ money($item['amount'] ?? 0) }}</td>
                                    <td class="text-end">{{ money($item['tax'] ?? 0) }}</td>
                                    <td class="text-end">{{ money(($item['amount'] ?? 0) + ($item['tax'] ?? 0)) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot class="table-light fw-semibold">
                                <tr>
                                    <td colspan="2">Subtotal</td>
                                    <td class="text-end" colspan="2">{{ money($bill->subtotal) }}</td>
                                    <td class="text-end">{{ money($bill->tax_amount) }}</td>
                                </tr>
                                @if ($bill->late_fee > 0)
                                <tr>
                                    <td colspan="4">Late Fee</td>
                                    <td class="text-end text-danger">+{{ money($bill->late_fee) }}</td>
                                </tr>
                                @endif
                                @if ($bill->discount > 0)
                                <tr>
                                    <td colspan="4">Discount / Waiver</td>
                                    <td class="text-end text-success">-{{ money($bill->discount) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="4">Grand Total</td>
                                    <td class="text-end">{{ money($bill->total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

                @if ($bill->notes)
                    <div class="alert alert-light mt-3 small">{{ $bill->notes }}</div>
                @endif
            </div>
        </div>

        {{-- Payments Table --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h3 class="h6 mb-0">Payments ({{ $bill->payments->count() }})</h3>
            </div>
            <div class="card-body">
                @forelse ($bill->payments as $payment)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-semibold small">{{ $payment->receipt_number }}</div>
                            <div class="text-muted" style="font-size:.8rem">
                                {{ ucfirst($payment->method) }}
                                @if ($payment->reference) · Ref: {{ $payment->reference }} @endif
                                · {{ $payment->paid_at?->format('d M Y H:i') }}
                            </div>
                            @if ($payment->notes)
                                <div class="text-muted" style="font-size:.75rem">{{ $payment->notes }}</div>
                            @endif
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold text-success">{{ money($payment->amount) }}</div>
                            <a href="{{ route('maintenance.receipt', [$bill, $payment]) }}"
                               class="btn btn-sm btn-link p-0" target="_blank">Receipt</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No payments recorded yet.</p>
                @endforelse
                @if ($bill->payments->count())
                    <div class="d-flex justify-content-between pt-2 fw-semibold small">
                        <span>Total Paid</span>
                        <span class="text-success">{{ money($bill->paid_amount) }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Late Fees --}}
        @if ($bill->lateFees->count())
        <div class="card shadow-sm">
            <div class="card-header py-2">
                <h3 class="h6 mb-0">Late Fees</h3>
            </div>
            <div class="card-body">
                @foreach ($bill->lateFees as $lf)
                    <div class="d-flex justify-content-between py-1 border-bottom small">
                        <div>
                            <div>Applied on {{ $lf->applied_on?->format('d M Y') }}</div>
                            @if ($lf->reason)<div class="text-muted">{{ $lf->reason }}</div>@endif
                        </div>
                        <span class="text-danger fw-semibold">{{ money($lf->amount) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar Summary --}}
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h3 class="h6 mb-3">Summary</h3>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Subtotal</td><td class="text-end">{{ money($bill->subtotal) }}</td></tr>
                    <tr><td class="text-muted">GST / Tax</td><td class="text-end">{{ money($bill->tax_amount) }}</td></tr>
                    @if ($bill->late_fee > 0)
                    <tr><td class="text-muted text-danger">Late Fee</td><td class="text-end text-danger">{{ money($bill->late_fee) }}</td></tr>
                    @endif
                    @if ($bill->discount > 0)
                    <tr><td class="text-muted text-success">Discount</td><td class="text-end text-success">-{{ money($bill->discount) }}</td></tr>
                    @endif
                    <tr class="fw-bold"><td>Total</td><td class="text-end">{{ money($bill->total) }}</td></tr>
                    <tr class="text-success"><td>Paid</td><td class="text-end">{{ money($bill->paid_amount) }}</td></tr>
                    <tr class="fw-bold {{ $bill->balance > 0 ? 'text-danger' : 'text-success' }}">
                        <td>Balance</td><td class="text-end">{{ money($bill->balance) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="{{ route('maintenance.invoice', $bill) }}" class="btn btn-outline-primary" target="_blank">
                <i class="bi bi-file-earmark-text"></i> View Invoice
            </a>
            <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Bills
            </a>
        </div>
    </div>
</div>

{{-- Record Payment Modal --}}
@can('collect', $bill)
@if (!in_array($bill->status, ['paid', 'cancelled']))
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('maintenance.pay', $bill) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        Balance due: <strong>{{ money($bill->balance) }}</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0.01"
                                   max="{{ $bill->balance }}"
                                   name="amount"
                                   value="{{ old('amount', $bill->balance) }}"
                                   class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Method <span class="text-danger">*</span></label>
                        <select name="method" class="form-select" required>
                            @foreach (['cash' => 'Cash', 'cheque' => 'Cheque', 'online' => 'Online', 'upi' => 'UPI', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer'] as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference / Transaction ID</label>
                        <input type="text" name="reference" class="form-control" placeholder="Cheque no., UTR, etc.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="datetime-local" name="paid_at" class="form-control"
                               value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

{{-- Waive Modal --}}
@can('waive', $bill)
@if (!in_array($bill->status, ['paid', 'cancelled']))
<div class="modal fade" id="waiveModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('maintenance.waive', $bill) }}" data-confirm="Waive this bill? This cannot be undone.">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Waive Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-warning small">This will cancel the outstanding balance of <strong>{{ money($bill->balance) }}</strong>.</p>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" rows="3" class="form-control" placeholder="Reason for waiving..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm Waive</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endcan
@endsection
