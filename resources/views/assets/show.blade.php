@extends('layouts.app')
@section('title', $asset->name)

@section('page-actions')
    @can('update', $asset)
        <a href="{{ route('assets.edit', $asset) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    @endcan
    @can('update', $asset)
        <form method="POST" action="{{ route('assets.depreciate', $asset) }}" class="d-inline">
            @csrf
            <button class="btn btn-outline-info ms-1"><i class="bi bi-arrow-repeat"></i> Recompute</button>
        </form>
    @endcan
    @can('delete', $asset)
        <form method="POST" action="{{ route('assets.destroy', $asset) }}" class="d-inline" data-confirm="Delete this asset?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger ms-1"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    {{-- Asset detail + depreciation --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h2 class="h5 mb-0">{{ $asset->name }}</h2>
                    @if ($asset->code)
                        <span class="text-muted small">{{ $asset->code }}</span>
                    @endif
                </div>
                <span class="badge text-bg-{{ match($asset->status) {
                    'active'            => 'success',
                    'under_maintenance' => 'warning',
                    'retired'           => 'secondary',
                    'disposed'          => 'danger',
                    default             => 'light',
                } }} text-capitalize">{{ str_replace('_', ' ', $asset->status) }}</span>
            </div>

            @if ($asset->description)
                <p class="text-muted">{{ $asset->description }}</p>
            @endif

            <div class="row g-2 small text-muted">
                <div class="col-sm-4"><strong>Category:</strong> {{ $asset->category?->name ?? '—' }}</div>
                <div class="col-sm-4"><strong>Location:</strong> {{ $asset->location ?? '—' }}</div>
                <div class="col-sm-4"><strong>Tower:</strong> {{ $asset->tower?->name ?? '—' }}</div>
                <div class="col-sm-4 mt-1"><strong>Purchase Date:</strong> {{ $asset->purchase_date?->format('d M Y') ?? '—' }}</div>
                <div class="col-sm-4 mt-1"><strong>Purchase Cost:</strong> {{ money($asset->purchase_cost) }}</div>
                <div class="col-sm-4 mt-1"><strong>Salvage Value:</strong> {{ money($asset->salvage_value) }}</div>
                <div class="col-sm-4 mt-1"><strong>Depreciation Method:</strong> {{ ucfirst(str_replace('_', ' ', $asset->depreciation_method)) }}</div>
                <div class="col-sm-4 mt-1"><strong>Depreciation Rate:</strong>
                    {{ $asset->depreciation_rate ? $asset->depreciation_rate.'%' : ($asset->category?->depreciation_rate ? $asset->category->depreciation_rate.'% (cat.)' : '—') }}
                </div>
                <div class="col-sm-4 mt-1"><strong>Useful Life:</strong>
                    {{ ($asset->useful_life_years ?? $asset->category?->useful_life_years) ? ($asset->useful_life_years ?? $asset->category->useful_life_years).' yrs' : '—' }}
                </div>
                <div class="col-sm-4 mt-1"><strong>Current Value:</strong>
                    <span class="fw-semibold text-success">{{ money($asset->current_value) }}</span>
                </div>
                <div class="col-sm-4 mt-1"><strong>Warranty Until:</strong>
                    @if ($asset->warranty_until)
                        @if ($asset->warranty_until->isPast())
                            <span class="text-danger">Expired {{ $asset->warranty_until->format('d M Y') }}</span>
                        @else
                            {{ $asset->warranty_until->format('d M Y') }}
                        @endif
                    @else
                        —
                    @endif
                </div>
            </div>
        </div></div>

        {{-- Depreciation Table --}}
        @if (count($depreciationTable) > 0)
        <div class="card shadow-sm mb-3"><div class="card-body">
            <h3 class="h6 mb-3">Depreciation Schedule</h3>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Year</th><th>Age (Years)</th><th>Book Value</th></tr></thead>
                    <tbody>
                    @foreach ($depreciationTable as $row)
                        <tr class="{{ $row['age_years'] === 0 ? 'table-light fw-semibold' : '' }}">
                            <td>{{ $row['year'] }}</td>
                            <td>{{ $row['age_years'] }}</td>
                            <td>{{ money($row['current_value']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div></div>
        @endif

        {{-- Maintenance Schedules --}}
        <div class="card shadow-sm mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h6 mb-0">Maintenance Schedules</h3>
                @can('schedule', $asset)
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                        <i class="bi bi-plus-lg"></i> Add Schedule
                    </button>
                @endcan
            </div>
            @if ($asset->schedules->isEmpty())
                <p class="text-muted small mb-0">No schedules added yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Title</th><th>Frequency</th><th>Next Due</th><th>Last Done</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($asset->schedules as $schedule)
                            <tr>
                                <td>{{ $schedule->title }}</td>
                                <td class="text-capitalize text-muted small">{{ str_replace('_', ' ', $schedule->frequency) }}</td>
                                <td class="text-muted small">
                                    @if ($schedule->next_due_date)
                                        @if ($schedule->next_due_date->isPast() && $schedule->status !== 'completed')
                                            <span class="text-danger fw-semibold">{{ $schedule->next_due_date->format('d M Y') }}</span>
                                        @else
                                            {{ $schedule->next_due_date->format('d M Y') }}
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $schedule->last_done_date?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    <span class="badge text-bg-{{ match($schedule->status) {
                                        'scheduled' => 'info',
                                        'due'       => 'warning',
                                        'completed' => 'success',
                                        'overdue'   => 'danger',
                                        default     => 'secondary',
                                    } }} text-capitalize">{{ $schedule->status }}</span>
                                </td>
                                <td>
                                    @can('update', $schedule)
                                        @if ($schedule->status !== 'completed' || $schedule->frequency !== 'one_time')
                                            <button class="btn btn-sm btn-outline-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#completeModal"
                                                data-schedule-id="{{ $schedule->id }}"
                                                data-schedule-title="{{ $schedule->title }}">
                                                <i class="bi bi-check-lg"></i> Log
                                            </button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div></div>

        {{-- Maintenance Logs --}}
        <div class="card shadow-sm"><div class="card-body">
            <h3 class="h6 mb-3">Maintenance History</h3>
            @if ($asset->logs->isEmpty())
                <p class="text-muted small mb-0">No maintenance logs yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Performed On</th><th>Performed By</th><th>Cost</th><th>Notes</th></tr></thead>
                        <tbody>
                        @foreach ($asset->logs as $log)
                            <tr>
                                <td>{{ $log->performed_on->format('d M Y') }}</td>
                                <td>{{ $log->performed_by ?? '—' }}</td>
                                <td>{{ money($log->cost) }}</td>
                                <td class="text-muted small">{{ \Illuminate\Support\Str::limit($log->notes ?? '', 60) ?: '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div></div>
    </div>

    {{-- Sidebar info --}}
    <div class="col-lg-4">
        @if ($asset->image)
            <div class="card shadow-sm mb-3"><div class="card-body p-0">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($asset->image) }}"
                     alt="{{ $asset->name }}" class="img-fluid rounded" style="max-height:200px;width:100%;object-fit:cover">
            </div></div>
        @endif

        <div class="card shadow-sm"><div class="card-body">
            <h3 class="h6 mb-3">Financial Summary</h3>
            <dl class="row small mb-0">
                <dt class="col-7">Purchase Cost</dt>
                <dd class="col-5 text-end">{{ money($asset->purchase_cost) }}</dd>
                <dt class="col-7">Salvage Value</dt>
                <dd class="col-5 text-end">{{ money($asset->salvage_value) }}</dd>
                <dt class="col-7">Current Value</dt>
                <dd class="col-5 text-end text-success fw-semibold">{{ money($asset->current_value) }}</dd>
                <dt class="col-7">Total Depreciated</dt>
                <dd class="col-5 text-end text-danger">
                    {{ money(max(0, ($asset->purchase_cost ?? 0) - ($asset->current_value ?? $asset->purchase_cost))) }}
                </dd>
            </dl>
        </div></div>
    </div>
</div>

{{-- Add Schedule Modal --}}
@can('schedule', $asset)
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('assets.schedule', $asset) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Maintenance Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Frequency</label>
                            <select name="frequency" class="form-select">
                                @foreach (['weekly', 'monthly', 'quarterly', 'half_yearly', 'yearly', 'one_time'] as $f)
                                    <option value="{{ $f }}" @selected($f === 'monthly')>{{ ucfirst(str_replace('_', ' ', $f)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Due Date</label>
                            <input type="date" name="next_due_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estimated Cost (₹)</label>
                            <input type="number" name="estimated_cost" class="form-control" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- Complete Maintenance Modal --}}
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="completeForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Log Maintenance: <span id="completeScheduleTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Performed On</label>
                            <input type="date" name="performed_on" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cost (₹)</label>
                            <input type="number" name="cost" class="form-control" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Performed By</label>
                            <input type="text" name="performed_by" class="form-control" placeholder="Name or team">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success">Log Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('completeModal').addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    var scheduleId    = btn.getAttribute('data-schedule-id');
    var scheduleTitle = btn.getAttribute('data-schedule-title');

    document.getElementById('completeScheduleTitle').textContent = scheduleTitle;
    document.getElementById('completeForm').action = '/schedules/' + scheduleId + '/complete';
});
</script>
@endpush
@endsection
