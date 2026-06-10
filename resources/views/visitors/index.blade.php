@extends('layouts.app')
@section('title', 'Visitors')

@section('page-actions')
    @can('create', App\Models\VisitorPass::class)
        <a href="{{ route('visitors.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Pass</a>
    @endcan
    @can('checkin', App\Models\VisitorLog::class)
        <a href="{{ route('visitors.gate') }}" class="btn btn-outline-secondary ms-2"><i class="bi bi-door-open"></i> Gate Console</a>
    @endcan
    @can('export', App\Models\VisitorPass::class)
        <a href="{{ route('visitors.export') }}" class="btn btn-outline-secondary ms-2"><i class="bi bi-download"></i> Export</a>
    @endcan
@endsection

@section('content')
{{-- KPI Cards --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body py-2">
            <div class="text-muted small">Inside Now</div>
            <div class="h5 mb-0 text-success">{{ $kpi['in_today'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body py-2">
            <div class="text-muted small">Checked Out Today</div>
            <div class="h5 mb-0 text-secondary">{{ $kpi['checked_out'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body py-2">
            <div class="text-muted small">Expected Today</div>
            <div class="h5 mb-0 text-primary">{{ $kpi['expected'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body py-2">
            <div class="text-muted small">Pending Approval</div>
            <div class="h5 mb-0 text-warning">{{ $kpi['pending'] }}</div>
        </div></div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3" id="visitorTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-panel" type="button" role="tab">
            <i class="bi bi-journal-check"></i> Visitor Logs
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="passes-tab" data-bs-toggle="tab" data-bs-target="#passes-panel" type="button" role="tab">
            <i class="bi bi-ticket-perforated"></i> Visitor Passes
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- Logs Tab --}}
    <div class="tab-pane fade show active" id="logs-panel" role="tabpanel">
        <div class="card shadow-sm"><div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead>
                        <tr>
                            <th>Name</th><th>Type</th><th>Flat</th><th>Gate</th>
                            <th>Status</th><th>Checked In</th><th>Checked Out</th><th>Guard</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="fw-semibold">{{ $log->name }}</td>
                            <td><span class="badge text-bg-light text-capitalize">{{ $log->type }}</span></td>
                            <td>{{ $log->flat?->number ?? '—' }}</td>
                            <td>{{ $log->gate ?? '—' }}</td>
                            <td>
                                @if ($log->status === 'in')
                                    <span class="badge text-bg-success">Inside</span>
                                @else
                                    <span class="badge text-bg-secondary">Out</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $log->checked_in_at->format('d M H:i') }}</td>
                            <td class="text-muted small">{{ $log->checked_out_at?->format('d M H:i') ?? '—' }}</td>
                            <td class="text-muted small">{{ $log->guardUser?->name ?? '—' }}</td>
                            <td class="text-end">
                                @can('checkout', $log)
                                    @if ($log->status === 'in')
                                        <form method="POST" action="{{ route('visitors.checkout', $log) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" title="Check out">
                                                <i class="bi bi-box-arrow-right"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No visitor logs yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>

    {{-- Passes Tab --}}
    <div class="tab-pane fade" id="passes-panel" role="tabpanel">
        <div class="card shadow-sm"><div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead>
                        <tr>
                            <th>Code</th><th>Name</th><th>Type</th><th>Host</th>
                            <th>Expected</th><th>Valid Until</th><th>Status</th><th>Entries</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($passes as $pass)
                        <tr>
                            <td><a href="{{ route('visitors.show', $pass) }}" class="fw-semibold font-monospace small">{{ $pass->code }}</a></td>
                            <td>{{ $pass->name }}</td>
                            <td><span class="badge text-bg-light text-capitalize">{{ $pass->type }}</span></td>
                            <td>{{ $pass->host?->name ?? '—' }}</td>
                            <td class="text-muted small">{{ $pass->expected_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="text-muted small">{{ $pass->valid_until?->format('d M Y H:i') ?? '—' }}</td>
                            <td>
                                <span class="badge text-capitalize
                                    @if($pass->status === 'approved') text-bg-success
                                    @elseif($pass->status === 'pending') text-bg-warning
                                    @elseif($pass->status === 'rejected') text-bg-danger
                                    @elseif($pass->status === 'used') text-bg-secondary
                                    @else text-bg-light @endif
                                ">{{ $pass->status }}</span>
                            </td>
                            <td class="text-muted small">{{ $pass->entries_used }}/{{ $pass->max_entries }}</td>
                            <td class="text-end"><a href="{{ route('visitors.show', $pass) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No visitor passes yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>

</div>
@endsection
