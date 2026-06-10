@extends('layouts.app')
@section('title', 'Trial Balance')

@section('page-actions')
    @can('accounting.reports')
        <a href="{{ request()->fullUrlWithQuery(['csv' => 1]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    @endcan
@endsection

@section('content')
{{-- Date filter --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('accounting.reports.trial') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1 small">As of Date</label>
                <input type="date" name="as_of" value="{{ $asOf->toDateString() }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

@php
    $totalDebit  = array_sum(array_column($rows, 'total_debit'));
    $totalCredit = array_sum(array_column($rows, 'total_credit'));
    $balanced    = abs($totalDebit - $totalCredit) < 0.001;
@endphp

@if (! $balanced)
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Trial balance is <strong>not balanced</strong>! Debits: {{ money($totalDebit) }} | Credits: {{ money($totalCredit) }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header">
        <span class="fw-semibold">Trial Balance</span>
        <span class="text-muted ms-2 small">as of {{ $asOf->format('d M Y') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Net</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-muted small">{{ $row['account_code'] ?? '—' }}</td>
                        <td class="fw-semibold">{{ $row['account_name'] }}</td>
                        <td><span class="badge text-bg-light text-capitalize">{{ $row['account_type'] }}</span></td>
                        <td class="text-end">{{ $row['total_debit'] > 0 ? money($row['total_debit']) : '—' }}</td>
                        <td class="text-end">{{ $row['total_credit'] > 0 ? money($row['total_credit']) : '—' }}</td>
                        <td class="text-end fw-semibold {{ $row['net'] >= 0 ? 'text-primary' : 'text-danger' }}">
                            {{ money($row['net']) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No posted entries found.</td></tr>
                @endforelse
                </tbody>
                @if (count($rows) > 0)
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="3">Grand Total</td>
                        <td class="text-end">{{ money($totalDebit) }}</td>
                        <td class="text-end">{{ money($totalCredit) }}</td>
                        <td class="text-end {{ $balanced ? 'text-success' : 'text-danger' }}">
                            {{ money($totalDebit - $totalCredit) }}
                            @if ($balanced) <i class="bi bi-check-circle-fill ms-1"></i> @endif
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
