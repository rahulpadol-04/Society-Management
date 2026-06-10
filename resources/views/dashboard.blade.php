@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row g-3 mb-1">
    @php
        $cards = [
            ['Residents', $stats['residents'], null, 'bi-people', 'primary'],
            ['Total Units', $stats['total_units'], null, 'bi-house-door', 'info'],
            ['Occupied Units', $stats['occupied_units'], null, 'bi-house-check', 'success'],
            ['Open Complaints', $stats['open_complaints'], null, 'bi-exclamation-octagon', 'danger'],
            ['Visitors Today', $stats['visitors_today'], null, 'bi-door-open', 'warning'],
            ['Pending Dues', money_short($stats['pending_dues']), money($stats['pending_dues']), 'bi-cash-coin', 'dark'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $title, $icon, $color])
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-soft-{{ $color }}"><i class="bi {{ $icon }}"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value text-truncate" @if($title) title="{{ $title }}" @endif>{{ $value }}</div>
                        <div class="stat-label">{{ $label }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-8">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6 mb-3">Maintenance Collection <span class="text-muted fw-normal small">— Billed vs Collected</span></h2>
            <div class="chart-box h-md"><canvas data-chart="maintenance-collection" data-type="bar"></canvas></div>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6 mb-3">Occupancy</h2>
            <div class="chart-box h-md"><canvas data-chart="occupancy" data-type="doughnut"></canvas></div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h2 class="h6 mb-3">Visitor Trends <span class="text-muted fw-normal small">— last 14 days</span></h2>
            <div class="chart-box h-sm"><canvas data-chart="visitor-trends" data-type="line"></canvas></div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h2 class="h6 mb-3">Complaint Trends <span class="text-muted fw-normal small">— last 14 days</span></h2>
            <div class="chart-box h-sm"><canvas data-chart="complaint-trends" data-type="line"></canvas></div>
        </div></div>
    </div>
    <div class="col-lg-12">
        <div class="card"><div class="card-body">
            <h2 class="h6 mb-3">Facility Usage <span class="text-muted fw-normal small">— last 30 days</span></h2>
            <div class="chart-box h-sm"><canvas data-chart="facility-usage" data-type="bar"></canvas></div>
        </div></div>
    </div>
</div>
@endsection
