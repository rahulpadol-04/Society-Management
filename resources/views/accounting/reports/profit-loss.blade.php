@extends('layouts.app')
@section('title', 'Profit & Loss Statement')

@section('page-actions')
    @can('accounting.reports')
        <a href="{{ request()->fullUrlWithQuery(['csv' => 1]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    @endcan
@endsection

@section('content')
{{-- Date filters --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('accounting.reports.pl') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1 small">From</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">To</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    {{-- Income --}}
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold text-success d-flex justify-content-between">
                <span><i class="bi bi-arrow-down-circle me-1"></i> Income</span>
                <span>{{ money($report['total_income']) }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <tbody>
                    @forelse ($report['income'] as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end fw-semibold text-success">{{ money($row['amount']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-muted text-center py-3">No income entries.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total Income</td>
                            <td class="text-end text-success">{{ money($report['total_income']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Expenses --}}
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold text-danger d-flex justify-content-between">
                <span><i class="bi bi-arrow-up-circle me-1"></i> Expenses</span>
                <span>{{ money($report['total_expense']) }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <tbody>
                    @forelse ($report['expenses'] as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end fw-semibold text-danger">{{ money($row['amount']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-muted text-center py-3">No expense entries.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total Expenses</td>
                            <td class="text-end text-danger">{{ money($report['total_expense']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Net Surplus --}}
    <div class="col-12">
        <div class="card border-{{ $report['net_surplus'] >= 0 ? 'success' : 'danger' }} shadow-sm">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-5">
                    {{ $report['net_surplus'] >= 0 ? 'Net Surplus' : 'Net Deficit' }}
                    <span class="text-muted small fw-normal ms-2">
                        ({{ $from->format('d M Y') }} — {{ $to->format('d M Y') }})
                    </span>
                </span>
                <span class="fw-bold fs-4 {{ $report['net_surplus'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ money($report['net_surplus']) }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
