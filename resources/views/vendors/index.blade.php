@extends('layouts.app')
@section('title', 'Vendors')

@section('page-actions')
    @can('create', App\Models\Vendor::class)
        <a href="{{ route('vendors.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Vendor</a>
    @endcan
    <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary ms-1"><i class="bi bi-clipboard-check"></i> Work Orders</a>
@endsection

@section('content')
{{-- KPI stat cards --}}
<div class="row g-2 mb-3">
    @foreach (['active' => 'success', 'inactive' => 'warning', 'blacklisted' => 'danger'] as $status => $color)
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm"><div class="card-body py-2">
                <div class="text-muted small text-capitalize">{{ ucfirst($status) }}</div>
                <div class="h5 mb-0 text-{{ $color }}">{{ $statusCounts[$status] ?? 0 }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Name</th><th>Company</th><th>Category</th>
                    <th>Contact</th><th>Rating</th><th>Status</th>
                    <th>Work Orders</th><th>Contracts</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($vendors as $vendor)
                <tr>
                    <td>
                        <a href="{{ route('vendors.show', $vendor) }}" class="fw-semibold">{{ $vendor->name }}</a>
                    </td>
                    <td>{{ $vendor->company ?? '—' }}</td>
                    <td>
                        <span class="badge text-bg-light text-capitalize">{{ str_replace('_', ' ', $vendor->category) }}</span>
                    </td>
                    <td class="small text-muted">{{ $vendor->phone ?? $vendor->email ?? '—' }}</td>
                    <td>
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= round($vendor->rating) ? '-fill text-warning' : ' text-muted' }}" style="font-size:.75rem"></i>
                        @endfor
                        <span class="small text-muted ms-1">{{ number_format($vendor->rating, 1) }}</span>
                    </td>
                    <td>
                        <span class="badge text-bg-{{ $vendor->status === 'active' ? 'success' : ($vendor->status === 'blacklisted' ? 'danger' : 'warning') }} text-capitalize">
                            {{ ucfirst($vendor->status) }}
                        </span>
                    </td>
                    <td class="text-center">{{ $vendor->work_orders_count }}</td>
                    <td class="text-center">{{ $vendor->contracts_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        @can('update', $vendor)
                            <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No vendors yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
