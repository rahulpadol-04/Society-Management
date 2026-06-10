@extends('layouts.app')
@section('title', 'Reports')

@section('content')

{{-- Headline KPI stat-cards --}}
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['Total Bills',        $kpis['total_bills'],                               'bi-receipt',        'primary'],
            ['Total Complaints',   $kpis['total_complaints'],                           'bi-exclamation-octagon','danger'],
            ['Visitors (All)',     $kpis['total_visitors'],                             'bi-door-open',      'warning'],
            ['Total Flats',        $kpis['total_flats'],                                'bi-house-door',     'info'],
            ['Occupied Flats',     $kpis['occupied_flats'],                             'bi-house-check',    'success'],
            ['Collections (MTD)',  money_short($kpis['collections_mtd']),               'bi-cash-coin',      'dark'],
        ];
    @endphp

    @foreach ($cards as [$label, $value, $icon, $color])
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-soft-{{ $color }}"><i class="bi {{ $icon }}"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value text-truncate">{{ $value }}</div>
                        <div class="stat-label">{{ $label }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Report cards grid --}}
@php
    $reports = [
        [
            'type'  => 'visitor',
            'icon'  => 'bi-door-open',
            'color' => 'warning',
            'title' => 'Visitor Report',
            'desc'  => 'Daily visitor counts, entry/exit trends, and visitor type breakdown.',
        ],
        [
            'type'  => 'billing',
            'icon'  => 'bi-cash-stack',
            'color' => 'primary',
            'title' => 'Billing Report',
            'desc'  => 'Maintenance bills billed, paid, and outstanding per billing period.',
        ],
        [
            'type'  => 'collection',
            'icon'  => 'bi-cash-coin',
            'color' => 'success',
            'title' => 'Collection Report',
            'desc'  => 'Payment collections by month and by payment method (cash, UPI, online, etc.).',
        ],
        [
            'type'  => 'complaint',
            'icon'  => 'bi-exclamation-octagon',
            'color' => 'danger',
            'title' => 'Complaint Report',
            'desc'  => 'Complaints by status and category, resolution rate, and average resolution time.',
        ],
        [
            'type'  => 'facility',
            'icon'  => 'bi-calendar-check',
            'color' => 'info',
            'title' => 'Facility Report',
            'desc'  => 'Facility bookings by facility name and booking status with revenue.',
        ],
        [
            'type'  => 'occupancy',
            'icon'  => 'bi-house-check',
            'color' => 'secondary',
            'title' => 'Occupancy Report',
            'desc'  => 'Flat occupancy status overall and per tower/block with occupancy percentage.',
        ],
        [
            'type'  => 'financial',
            'icon'  => 'bi-journals',
            'color' => 'dark',
            'title' => 'Financial Report',
            'desc'  => 'Income vs expense with net surplus — from accounting ledgers or maintenance collections.',
        ],
    ];
@endphp

<div class="row g-3">
    @foreach ($reports as $r)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="stat-icon bg-soft-{{ $r['color'] }}" style="flex-shrink:0">
                            <i class="bi {{ $r['icon'] }} fs-5"></i>
                        </span>
                        <h2 class="h6 mb-0">{{ $r['title'] }}</h2>
                    </div>
                    <p class="text-muted small flex-grow-1">{{ $r['desc'] }}</p>
                    <div class="d-flex gap-2 mt-2">
                        <a href="{{ route('reports.show', $r['type']) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye me-1"></i>View Report
                        </a>
                        @can('reports.export')
                            <a href="{{ route('reports.export', $r['type']) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download me-1"></i>CSV
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection
