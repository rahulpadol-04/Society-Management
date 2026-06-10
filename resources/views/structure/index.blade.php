@extends('layouts.app')
@section('title', 'Towers & Units')

@section('page-actions')
    @can('create', App\Models\Tower::class)
        <a href="{{ route('towers.create') }}" class="btn btn-outline-primary"><i class="bi bi-building-add"></i> Add Tower</a>
        <a href="{{ route('flats.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Unit</a>
    @endcan
@endsection

@section('content')
<div class="row g-2 mb-3">
    @php
        $cards = [
            ['Towers', $summary['towers'], 'primary'],
            ['Total Units', $summary['flats'], 'info'],
            ['Occupied', $summary['counts']['occupied'] ?? 0, 'success'],
            ['Vacant', $summary['counts']['vacant'] ?? 0, 'warning'],
            ['Occupancy', $summary['occupancy'].'%', 'dark'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $color])
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm"><div class="card-body py-2">
                <div class="text-muted small">{{ $label }}</div>
                <div class="h5 mb-0 text-{{ $color }}">{{ $value }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-units" type="button">Units</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-towers" type="button">Towers</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-units">
        <div class="card shadow-sm"><div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead><tr><th>Unit</th><th>Tower</th><th>Type</th><th>Area</th><th>Ownership</th><th>Owner</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($flats as $flat)
                        <tr>
                            <td><a href="{{ route('flats.show', $flat) }}" class="fw-semibold">{{ $flat->number }}</a></td>
                            <td>{{ $flat->tower?->name ?? '—' }}</td>
                            <td>{{ $flat->type ?? '—' }}</td>
                            <td>{{ $flat->carpet_area ? $flat->carpet_area.' sqft' : '—' }}</td>
                            <td class="text-capitalize">{{ str_replace('_', ' ', $flat->ownership) }}</td>
                            <td>{{ $flat->owner?->name ?? '—' }}</td>
                            <td><span class="badge text-bg-{{ in_array($flat->status, ['occupied','on_rent']) ? 'success' : 'secondary' }} text-capitalize">{{ str_replace('_', ' ', $flat->status) }}</span></td>
                            <td class="text-end"><a href="{{ route('flats.show', $flat) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No units yet. Add a tower and generate units, or add units individually.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>

    <div class="tab-pane fade" id="tab-towers">
        <div class="row g-3">
            @forelse ($towers as $tower)
                <div class="col-md-4">
                    <div class="card shadow-sm h-100"><div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h2 class="h6 mb-0"><i class="bi bi-building me-1"></i>{{ $tower->name }}</h2>
                            <span class="badge text-bg-light text-capitalize">{{ $tower->type }}</span>
                        </div>
                        <div class="text-muted small mt-2">
                            {{ $tower->total_floors }} floors · {{ $tower->flats_count }} units
                        </div>
                        <a href="{{ route('towers.show', $tower) }}" class="btn btn-sm btn-outline-primary mt-3">Manage</a>
                    </div></div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-light border text-center">No towers defined yet.</div></div>
            @endforelse
        </div>
    </div>
</div>
@endsection
