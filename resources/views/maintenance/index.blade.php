@extends('layouts.app')
@section('title', 'Maintenance Billing')

@section('page-actions')
    @can('generate', App\Models\MaintenanceBill::class)
        <a href="{{ route('maintenance.generate') }}" class="btn btn-primary"><i class="bi bi-lightning"></i> Generate Bills</a>
    @endcan
    @can('viewAny', App\Models\MaintenanceHead::class)
        <a href="{{ route('maintenance.heads.index') }}" class="btn btn-outline-secondary ms-2"><i class="bi bi-list-ul"></i> Manage Heads</a>
    @endcan
    @can('export', App\Models\MaintenanceBill::class)
        <a href="{{ route('maintenance.export') }}" class="btn btn-outline-success ms-2"><i class="bi bi-download"></i> Export CSV</a>
    @endcan
@endsection

@section('content')

{{-- KPI Stat Cards --}}
<div class="row g-3 mb-3">
    @php
        $cards = [
            ['Total Billed',  money($totals['total_billed']),    'bi-cash-stack',     'primary'],
            ['Collected',     money($totals['total_paid']),      'bi-check-circle',   'success'],
            ['Outstanding',   money($totals['outstanding']),     'bi-exclamation-circle', 'warning'],
            ['Overdue Bills', $totals['overdue_count'],          'bi-alarm',          'danger'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $icon, $color])
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-soft-{{ $color }}"><i class="bi {{ $icon }}"></i></span>
                    <div>
                        <div class="stat-value">{{ $value }}</div>
                        <div class="stat-label">{{ $label }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Collection Chart --}}
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6 mb-3">Maintenance Collection <span class="text-muted fw-normal small">— Billed vs Collected</span></h2>
                <div class="chart-box h-sm"><canvas data-chart="maintenance-collection" data-type="bar"></canvas></div>
            </div>
        </div>
    </div>
</div>

{{-- Bills DataTable --}}
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Bill No.</th>
                        <th>Period</th>
                        <th>Flat</th>
                        <th>Resident</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($bills as $bill)
                    <tr>
                        <td>
                            <a href="{{ route('maintenance.bills.show', $bill) }}" class="fw-semibold">{{ $bill->bill_number }}</a>
                        </td>
                        <td>{{ $bill->period }}</td>
                        <td>{{ $bill->flat?->number ?? '—' }}</td>
                        <td>{{ $bill->resident?->name ?? '—' }}</td>
                        <td>{{ money($bill->total) }}</td>
                        <td>{{ money($bill->paid_amount) }}</td>
                        <td>
                            @if ($bill->balance > 0)
                                <span class="fw-semibold text-danger">{{ money($bill->balance) }}</span>
                            @else
                                <span class="text-success">Nil</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'unpaid'    => 'warning',
                                    'partial'   => 'info',
                                    'paid'      => 'success',
                                    'overdue'   => 'danger',
                                    'cancelled' => 'secondary',
                                ];
                            @endphp
                            <span class="badge text-bg-{{ $statusColors[$bill->status] ?? 'light' }} text-capitalize">
                                {{ $bill->status }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ $bill->due_date?->format('d M Y') }}
                            @if ($bill->isOverdue())
                                <span class="badge text-bg-danger ms-1">Overdue</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('maintenance.bills.show', $bill) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            No bills found. <a href="{{ route('maintenance.generate') }}">Generate bills</a> to get started.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
