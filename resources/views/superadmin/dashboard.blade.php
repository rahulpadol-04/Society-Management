@extends('layouts.app')
@section('title', 'Platform Dashboard')

@section('content')
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['Total Societies', $stats['total_societies'], 'bi-buildings', 'primary'],
            ['Active', $stats['active_societies'], 'bi-check-circle', 'success'],
            ['On Trial', $stats['trial_societies'], 'bi-hourglass-split', 'warning'],
            ['MRR', money($stats['mrr']), 'bi-graph-up', 'info'],
            ['Revenue (Month)', money($stats['revenue_this_month']), 'bi-cash-stack', 'dark'],
            ['New Inquiries', $stats['new_inquiries'], 'bi-envelope', 'secondary'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $icon, $color])
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-soft-{{ $color }}"><i class="bi {{ $icon }}"></i></span>
                <div>
                    <div class="stat-value">{{ $value }}</div>
                    <div class="stat-label">{{ $label }}</div>
                </div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-12">
        <div class="card"><div class="card-body">
            <h2 class="h6 mb-3">Monthly Revenue <span class="text-muted fw-normal small">— last 12 months</span></h2>
            <div class="chart-box h-md"><canvas data-chart="revenue" data-type="line"></canvas></div>
        </div></div>
    </div>
</div>
@endsection
