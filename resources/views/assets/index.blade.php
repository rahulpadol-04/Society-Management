@extends('layouts.app')
@section('title', 'Assets')

@section('page-actions')
    @can('create', App\Models\Asset::class)
        <a href="{{ route('assets.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Asset</a>
    @endcan
    @can('create', App\Models\AssetCategory::class)
        <a href="{{ route('assets.categories.index') }}" class="btn btn-outline-secondary ms-2"><i class="bi bi-tags"></i> Categories</a>
    @endcan
@endsection

@section('content')
{{-- KPI Cards --}}
<div class="row g-2 mb-3">
    @php
        $kpiCards = [
            ['Total Assets',        $kpi['total'],            'bi-box-seam',       'primary'],
            ['Purchase Value',       money($kpi['purchase_value']), 'bi-cash-stack', 'info'],
            ['Current Value',        money($kpi['current_value']),  'bi-graph-down-arrow', 'success'],
            ['Under Maintenance',    $kpi['under_maintenance'], 'bi-tools',         'warning'],
        ];
    @endphp
    @foreach ($kpiCards as [$label, $value, $icon, $color])
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

{{-- Filters --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach (['active', 'under_maintenance', 'retired', 'disposed'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-secondary">Filter</button>
                <a href="{{ route('assets.index') }}" class="btn btn-sm btn-link">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Code</th><th>Name</th><th>Category</th><th>Location</th>
                    <th>Purchase Cost</th><th>Current Value</th><th>Status</th><th>Warranty</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($assets as $asset)
                <tr>
                    <td class="text-muted small">{{ $asset->code ?? '—' }}</td>
                    <td><a href="{{ route('assets.show', $asset) }}" class="fw-semibold">{{ $asset->name }}</a></td>
                    <td>{{ $asset->category?->name ?? '—' }}</td>
                    <td>{{ $asset->location ?? '—' }}</td>
                    <td>{{ money($asset->purchase_cost) }}</td>
                    <td>{{ money($asset->current_value) }}</td>
                    <td>
                        <span class="badge text-bg-{{ match($asset->status) {
                            'active'            => 'success',
                            'under_maintenance' => 'warning',
                            'retired'           => 'secondary',
                            'disposed'          => 'danger',
                            default             => 'light',
                        } }} text-capitalize">{{ str_replace('_', ' ', $asset->status) }}</span>
                    </td>
                    <td class="text-muted small">
                        @if ($asset->warranty_until)
                            @if ($asset->warranty_until->isPast())
                                <span class="text-danger">Expired {{ $asset->warranty_until->format('d M Y') }}</span>
                            @else
                                {{ $asset->warranty_until->format('d M Y') }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('assets.show', $asset) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No assets found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
