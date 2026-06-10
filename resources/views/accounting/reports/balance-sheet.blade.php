@extends('layouts.app')
@section('title', 'Balance Sheet')

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
        <form method="GET" action="{{ route('accounting.reports.bs') }}" class="row g-2 align-items-end">
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
    $totalAssets      = $report['total_assets'];
    $totalLiabEq      = $report['total_liabilities'] + $report['total_equity'];
    $balanced         = abs($totalAssets - $totalLiabEq) < 0.01;
@endphp

@if (! $balanced)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Balance sheet is out of balance. Assets: {{ money($totalAssets) }} vs Liabilities + Equity: {{ money($totalLiabEq) }}
    </div>
@endif

<div class="row g-3">
    {{-- Assets --}}
    <div class="col-md-6">
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold d-flex justify-content-between">
                <span>Assets</span>
                <span>{{ money($totalAssets) }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <tbody>
                    @forelse ($report['assets'] as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end fw-semibold">{{ money($row['amount']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-muted text-center py-3">No asset entries.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total Assets</td>
                            <td class="text-end">{{ money($totalAssets) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Liabilities + Equity --}}
    <div class="col-md-6">
        {{-- Liabilities --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold d-flex justify-content-between">
                <span>Liabilities</span>
                <span>{{ money($report['total_liabilities']) }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <tbody>
                    @forelse ($report['liabilities'] as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end fw-semibold">{{ money($row['amount']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-muted text-center py-3">No liability entries.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total Liabilities</td>
                            <td class="text-end">{{ money($report['total_liabilities']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Equity --}}
        <div class="card shadow-sm">
            <div class="card-header fw-semibold d-flex justify-content-between">
                <span>Equity</span>
                <span>{{ money($report['total_equity']) }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <tbody>
                    @forelse ($report['equity'] as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end fw-semibold">{{ money($row['amount']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-muted small py-2 px-3">No equity accounts.</td></tr>
                    @endforelse
                    @if ($report['surplus'] != 0)
                    <tr class="table-light">
                        <td class="text-muted small">Retained Surplus / (Deficit)</td>
                        <td class="text-end fw-semibold {{ $report['surplus'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ money($report['surplus']) }}
                        </td>
                    </tr>
                    @endif
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Total Equity</td>
                            <td class="text-end">{{ money($report['total_equity']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Balance check --}}
        <div class="card mt-3 border-{{ $balanced ? 'success' : 'danger' }}">
            <div class="card-body py-2 d-flex justify-content-between fw-bold">
                <span>Total Liabilities + Equity</span>
                <span class="{{ $balanced ? 'text-success' : 'text-danger' }}">
                    {{ money($totalLiabEq) }}
                    @if ($balanced) <i class="bi bi-check-circle-fill ms-1"></i> @endif
                </span>
            </div>
        </div>
    </div>
</div>

<p class="text-muted small mt-3">
    <i class="bi bi-info-circle me-1"></i>
    Balance Sheet as of {{ $asOf->format('d M Y') }} · Based on posted journal entries only.
</p>
@endsection
