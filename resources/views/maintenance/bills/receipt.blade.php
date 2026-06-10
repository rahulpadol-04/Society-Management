@extends('layouts.app')
@section('title', 'Receipt '.$payment->receipt_number)

@push('styles')
<style>
    @media print {
        .app-wrapper { display: block !important; }
        .app-main { margin-left: 0 !important; }
        .no-print, nav, footer, .app-footer, #sidebarToggle { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('page-actions')
    <button class="btn btn-outline-secondary no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>
    <a href="{{ route('maintenance.bills.show', $bill) }}" class="btn btn-link no-print">Back to Bill</a>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
            <div>
                <h1 class="h4 text-success mb-0"><i class="bi bi-check-circle-fill me-2"></i>PAYMENT RECEIPT</h1>
                <div class="text-muted small">{{ $payment->receipt_number }}</div>
            </div>
            <div class="text-end">
                <div class="fw-bold">{{ $society?->name ?? config('app.name') }}</div>
                @if ($society?->email)
                    <div class="text-muted small">{{ $society->email }}</div>
                @endif
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-sm-6">
                <div class="fw-semibold mb-1">Received From:</div>
                <div>{{ $bill->resident?->name ?? 'Occupant' }}</div>
                <div class="text-muted small">Flat: {{ $bill->flat?->number ?? 'N/A' }}</div>
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <table class="table table-sm table-borderless mb-0 ms-auto" style="width:auto">
                    <tr><td class="text-muted pe-3">Receipt No.</td><td class="fw-semibold">{{ $payment->receipt_number }}</td></tr>
                    <tr><td class="text-muted pe-3">Bill No.</td><td>{{ $bill->bill_number }}</td></tr>
                    <tr><td class="text-muted pe-3">Period</td><td>{{ $bill->period }}</td></tr>
                    <tr><td class="text-muted pe-3">Paid On</td><td>{{ $payment->paid_at?->format('d M Y H:i') }}</td></tr>
                    <tr><td class="text-muted pe-3">Method</td><td class="text-capitalize">{{ str_replace('_', ' ', $payment->method) }}</td></tr>
                    @if ($payment->reference)
                    <tr><td class="text-muted pe-3">Reference</td><td>{{ $payment->reference }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="text-center py-3 mb-4" style="background:#f8fff8; border-radius:8px; border:2px dashed #198754;">
            <div class="text-muted small mb-1">Amount Paid</div>
            <div class="display-5 fw-bold text-success">{{ money($payment->amount) }}</div>
        </div>

        <div class="row small text-muted">
            <div class="col-sm-4"><strong>Bill Total:</strong> {{ money($bill->total) }}</div>
            <div class="col-sm-4"><strong>Total Paid:</strong> {{ money($bill->paid_amount) }}</div>
            <div class="col-sm-4"><strong>Balance:</strong>
                <span class="{{ $bill->balance > 0 ? 'text-danger' : 'text-success' }}">{{ money($bill->balance) }}</span>
            </div>
        </div>

        @if ($payment->notes)
            <div class="alert alert-light mt-3 small">{{ $payment->notes }}</div>
        @endif

        <div class="border-top pt-3 mt-3 text-muted small text-center">
            Recorded by: {{ $payment->recorder?->name ?? 'System' }} ·
            Generated: {{ now()->format('d M Y H:i') }}
        </div>
    </div>
</div>
</div>
</div>
@endsection
