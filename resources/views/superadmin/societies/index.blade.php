@extends('layouts.app')
@section('title', 'Societies')

@section('page-actions')
    @can('create', App\Models\Society::class)
        <a href="{{ route('societies.create') }}" class="btn btn-primary">
            <i class="bi bi-building-add"></i> New Society
        </a>
    @endcan
@endsection

@section('content')

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    @php
        $kpiCards = [
            ['Total Societies', $kpi['total'],     'bi-buildings',        'primary'],
            ['Active',          $kpi['active'],    'bi-check-circle',     'success'],
            ['On Trial',        $kpi['trial'],     'bi-hourglass-split',  'warning'],
            ['Suspended',       $kpi['suspended'], 'bi-slash-circle',     'danger'],
        ];
    @endphp
    @foreach ($kpiCards as [$label, $value, $icon, $color])
        <div class="col-6 col-md-3">
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

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Society</th>
                        <th>City</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Users</th>
                        <th>Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($societies as $society)
                    <tr>
                        <td>
                            <div class="fw-semibold">
                                <a href="{{ route('societies.show', $society) }}">{{ $society->name }}</a>
                            </div>
                            <div class="small text-muted">{{ $society->slug }}</div>
                        </td>
                        <td>{{ $society->city ?? '—' }}</td>
                        <td>{{ $society->plan?->name ?? '—' }}</td>
                        <td>
                            <span class="badge status-{{ $society->status }} text-capitalize">
                                {{ $society->status }}
                            </span>
                        </td>
                        <td>{{ $society->users_count }}</td>
                        <td>{{ $society->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('societies.show', $society) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('impersonate', $society)
                                    <form method="POST" action="{{ route('societies.impersonate', $society) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-info" title="Impersonate">
                                            <i class="bi bi-person-fill-gear"></i>
                                        </button>
                                    </form>
                                @endcan
                                @can('suspend', $society)
                                    <form method="POST" action="{{ route('societies.suspend', $society) }}"
                                          data-confirm="{{ $society->status === 'suspended' ? 'Reactivate this society?' : 'Suspend this society?' }}">
                                        @csrf
                                        <button class="btn btn-sm {{ $society->status === 'suspended' ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                                title="{{ $society->status === 'suspended' ? 'Reactivate' : 'Suspend' }}">
                                            <i class="bi {{ $society->status === 'suspended' ? 'bi-play-circle' : 'bi-pause-circle' }}"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No societies found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
