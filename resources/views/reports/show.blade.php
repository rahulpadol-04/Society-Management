@extends('layouts.app')
@section('title', $report['title'])

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">{{ $report['title'] }}</li>
@endsection

@section('page-actions')
    @can('reports.export')
        <a href="{{ route('reports.export', [$type, 'from' => $from, 'to' => $to]) }}" class="btn btn-outline-success">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    @endcan
    <a href="{{ route('reports.print', [$type, 'from' => $from, 'to' => $to]) }}" target="_blank" class="btn btn-outline-secondary ms-2">
        <i class="bi bi-printer me-1"></i>Print / PDF
    </a>
@endsection

@section('content')

{{-- Date filter form --}}
@if ($type !== 'occupancy')
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.show', $type) }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="{{ route('reports.show', $type) }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Summary stat-cards --}}
@if (!empty($report['summary']))
<div class="row g-3 mb-4">
    @foreach ($report['summary'] as $stat)
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-soft-{{ $stat['color'] }}"><i class="bi {{ $stat['icon'] }}"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value text-truncate">{{ $stat['value'] }}</div>
                        <div class="stat-label">{{ $stat['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif

{{-- Chart --}}
@if (!empty($report['chart']['labels']))
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h2 class="h6 mb-3">{{ $report['title'] }} — Chart</h2>
        <div class="chart-box h-md">
            <canvas id="reportChart"></canvas>
        </div>
    </div>
</div>
@endif

{{-- Main data table --}}
@if (!empty($report['columns']))
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        @foreach ($report['columns'] as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['columns']) }}" class="text-center text-muted py-4">
                                No data found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Extra sections (by_type, by_method, by_tower, by_category …) --}}
@foreach ($report['extras'] ?? [] as $extra)
    @if (!empty($extra['columns']) && !empty($extra['rows']))
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h3 class="h6 mb-0">{{ $extra['title'] }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead>
                        <tr>
                            @foreach ($extra['columns'] as $col)
                                <th>{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($extra['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($extra['columns']) }}" class="text-center text-muted py-4">No data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection

@push('scripts')
@if (!empty($report['chart']['labels']))
<script>
(function () {
    var ctx = document.getElementById('reportChart');
    if (!ctx) return;

    var chartData = @json($report['chart']);

    // Pick chart type based on number of datasets and report type
    var chartType = '{{ in_array($type, ["occupancy"]) ? "doughnut" : (count($report["chart"]["datasets"] ?? []) > 1 ? "bar" : "bar") }}';

    // Colour palette
    var palette = [
        'rgba(13,110,253,0.75)',
        'rgba(25,135,84,0.75)',
        'rgba(220,53,69,0.75)',
        'rgba(255,193,7,0.75)',
        'rgba(13,202,240,0.75)',
        'rgba(108,117,125,0.75)',
        'rgba(111,66,193,0.75)',
    ];

    chartData.datasets = chartData.datasets.map(function (ds, i) {
        ds.backgroundColor = chartType === 'doughnut'
            ? chartData.labels.map(function (_, j) { return palette[j % palette.length]; })
            : palette[i % palette.length];
        ds.borderColor = chartType === 'line' ? palette[i % palette.length] : 'transparent';
        ds.fill = false;
        return ds;
    });

    new Chart(ctx, {
        type: chartType,
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
            },
        },
    });
}());
</script>
@endif
@endpush
