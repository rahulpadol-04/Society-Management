@extends('layouts.app')
@section('title', 'Usage Analytics')

@section('content')

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['Total Societies',  $stats['total_societies'],    'bi-buildings',       'primary'],
            ['Active',           $stats['active_societies'],   'bi-check-circle',    'success'],
            ['On Trial',         $stats['trial_societies'],    'bi-hourglass-split', 'warning'],
            ['MRR',              money($stats['mrr']),         'bi-graph-up',        'info'],
            ['Revenue (Month)',  money($stats['revenue_this_month']), 'bi-cash-stack','dark'],
            ['New Inquiries',    $stats['new_inquiries'],      'bi-envelope',        'secondary'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $icon, $color])
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-soft-{{ $color }}"><i class="bi {{ $icon }}"></i></span>
                <div class="min-w-0">
                    <div class="stat-value text-truncate">{{ $value }}</div>
                    <div class="stat-label">{{ $label }}</div>
                </div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    {{-- Revenue chart --}}
    <div class="col-lg-8">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Monthly Revenue <span class="text-muted fw-normal small">— last 12 months</span></h2>
            <div class="chart-box h-md"><canvas data-chart="revenue" data-type="line"></canvas></div>
        </div></div>
    </div>

    {{-- Plan distribution --}}
    <div class="col-lg-4">
        <div class="card shadow-sm h-100"><div class="card-body">
            <h2 class="h6 mb-3">Plan Distribution</h2>
            @forelse ($planDistribution as $planName => $count)
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                    <span>{{ $planName ?? 'No Plan' }}</span>
                    <span class="badge text-bg-primary">{{ $count }}</span>
                </div>
                @php
                    $total = $planDistribution->sum() ?: 1;
                    $pct = round(($count / $total) * 100);
                @endphp
                <div class="progress mb-2" style="height:4px">
                    <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            @empty
                <p class="text-muted small mb-0">No data yet.</p>
            @endforelse
        </div></div>
    </div>
</div>

{{-- Societies growth --}}
@if ($growth->count())
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">New Societies — Last 12 Months</h2>
            <div class="d-flex flex-wrap gap-3">
                @foreach ($growth as $month => $count)
                    <div class="text-center small">
                        <div class="fw-bold">{{ $count }}</div>
                        <div class="text-muted">{{ $month }}</div>
                    </div>
                @endforeach
            </div>
        </div></div>
    </div>
</div>
@endif

{{-- Top societies by users --}}
<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h6 mb-3">Top Societies by Users</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle table-sm">
                <thead><tr><th>Society</th><th>City</th><th>Sub. Status</th><th class="text-end">Users</th></tr></thead>
                <tbody>
                @forelse ($topSocieties as $soc)
                    <tr>
                        <td><a href="{{ route('societies.show', $soc) }}">{{ $soc->name }}</a></td>
                        <td class="text-muted">{{ $soc->city ?? '—' }}</td>
                        <td>
                            <span class="badge status-{{ $soc->subscription_status }} text-capitalize">
                                {{ $soc->subscription_status }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold">{{ $soc->users_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No societies yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
