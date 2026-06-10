@extends('layouts.app')
@section('title', 'Invoice '.$bill->bill_number)

@push('styles')
<style>
    @media print {
        .app-wrapper { display: block !important; }
        .app-main { margin-left: 0 !important; }
        .no-print, nav, footer, .app-footer, #sidebarToggle { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
    .invoice-header { border-bottom: 2px solid #0d6efd; padding-bottom: 1rem; margin-bottom: 1.5rem; }
</style>
@endpush

@section('page-actions')
    <button class="btn btn-outline-secondary no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>
    <a href="{{ route('maintenance.bills.show', $bill) }}" class="btn btn-link no-print">Back</a>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card shadow-sm">
    <div class="card-body p-4">
        @if ($template?->header_html)
            {!! $template->header_html !!}
        @else
            <div class="invoice-header d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="h3 text-primary mb-0">TAX INVOICE</h1>
                    <div class="text-muted small">{{ $bill->bill_number }}</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold fs-5">{{ $society?->name ?? config('app.name') }}</div>
                    @if ($society?->address_line1)
                        <div class="text-muted small">{{ $society->address_line1 }}</div>
                    @endif
                    @if ($society?->city)
                        <div class="text-muted small">{{ $society->city }}{{ $society->state ? ', '.$society->state : '' }}</div>
                    @endif
                    @if ($society?->email)
                        <div class="text-muted small">{{ $society->email }}</div>
                    @endif
                </div>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-sm-6">
                <div class="fw-semibold mb-1">Bill To:</div>
                <div>{{ $bill->resident?->name ?? 'Occupant' }}</div>
                <div class="text-muted small">Flat: {{ $bill->flat?->number ?? 'N/A' }}</div>
                @if ($bill->resident?->email)
                    <div class="text-muted small">{{ $bill->resident->email }}</div>
                @endif
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <table class="table table-sm table-borderless mb-0 ms-auto" style="width:auto">
                    <tr><td class="text-muted pe-3">Bill Number</td><td class="fw-semibold">{{ $bill->bill_number }}</td></tr>
                    <tr><td class="text-muted pe-3">Period</td><td>{{ $bill->period }}</td></tr>
                    <tr><td class="text-muted pe-3">Bill Date</td><td>{{ $bill->bill_date?->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted pe-3">Due Date</td><td class="{{ $bill->isOverdue() ? 'text-danger fw-bold' : '' }}">{{ $bill->due_date?->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted pe-3">Status</td><td><span class="badge text-bg-{{ in_array($bill->status, ['paid']) ? 'success' : 'warning' }} text-capitalize">{{ $bill->status }}</span></td></tr>
                </table>
            </div>
        </div>

        {{-- Line Items --}}
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">GST</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                @if ($bill->line_items)
                    @foreach ($bill->line_items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item['head'] ?? '—' }}</td>
                        <td class="text-capitalize text-muted small">{{ str_replace('_', ' ', $item['type'] ?? '') }}</td>
                        <td class="text-end">{{ money($item['amount'] ?? 0) }}</td>
                        <td class="text-end">{{ money($item['tax'] ?? 0) }}</td>
                        <td class="text-end fw-semibold">{{ money(($item['amount'] ?? 0) + ($item['tax'] ?? 0)) }}</td>
                    </tr>
                    @endforeach
                @endif
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end">Subtotal</td>
                        <td class="text-end">{{ money($bill->subtotal) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end">GST / Tax</td>
                        <td class="text-end">{{ money($bill->tax_amount) }}</td>
                    </tr>
                    @if ($bill->late_fee > 0)
                    <tr>
                        <td colspan="5" class="text-end text-danger">Late Fee</td>
                        <td class="text-end text-danger">{{ money($bill->late_fee) }}</td>
                    </tr>
                    @endif
                    @if ($bill->discount > 0)
                    <tr>
                        <td colspan="5" class="text-end text-success">Discount / Waiver</td>
                        <td class="text-end text-success">-{{ money($bill->discount) }}</td>
                    </tr>
                    @endif
                    <tr class="fw-bold fs-5">
                        <td colspan="5" class="text-end">Grand Total</td>
                        <td class="text-end">{{ money($bill->total) }}</td>
                    </tr>
                    @if ($bill->paid_amount > 0)
                    <tr class="text-success">
                        <td colspan="5" class="text-end">Amount Paid</td>
                        <td class="text-end">{{ money($bill->paid_amount) }}</td>
                    </tr>
                    <tr class="fw-bold {{ $bill->balance > 0 ? 'text-danger' : 'text-success' }}">
                        <td colspan="5" class="text-end">Balance Due</td>
                        <td class="text-end">{{ money($bill->balance) }}</td>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>

        {{-- Terms --}}
        @if ($template?->terms)
            <div class="border-top pt-3 text-muted small">
                <strong>Terms & Conditions</strong><br>
                {!! $template->terms !!}
            </div>
        @else
            <div class="border-top pt-3 text-muted small">
                <strong>Note:</strong> Please pay before the due date to avoid late fees.
                Payments can be made via UPI, bank transfer, cheque, or cash at the society office.
            </div>
        @endif

        @if ($template?->footer_html)
            {!! $template->footer_html !!}
        @endif
    </div>
</div>
</div>
</div>
@endsection
