@extends('layouts.app')
@section('title', 'Residents')

@section('page-actions')
    @can('export', App\Models\Resident::class)
        <a href="{{ route('residents.export') }}" class="btn btn-outline-secondary"><i class="bi bi-download"></i> Export CSV</a>
    @endcan
    @can('create', App\Models\Resident::class)
        <a href="{{ route('residents.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Resident</a>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Name</th><th>Type</th><th>Flat</th><th>Phone</th>
                    <th>Status</th><th>Move-in</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($residents as $resident)
                <tr>
                    <td>
                        <a href="{{ route('residents.show', $resident) }}" class="fw-semibold">{{ $resident->name }}</a>
                        @if ($resident->is_primary)
                            <span class="badge text-bg-primary ms-1 small">Primary</span>
                        @endif
                    </td>
                    <td><span class="badge text-bg-light text-capitalize">{{ str_replace('_', ' ', $resident->type) }}</span></td>
                    <td>{{ $resident->flat?->number ?? '—' }}</td>
                    <td>{{ $resident->phone ?? '—' }}</td>
                    <td>
                        <span class="badge text-bg-{{ $resident->status === 'active' ? 'success' : ($resident->status === 'moved_out' ? 'secondary' : 'warning') }} text-capitalize">
                            {{ str_replace('_', ' ', $resident->status) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $resident->move_in_date?->format('d M Y') ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('residents.show', $resident) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        @can('update', $resident)
                            <a href="{{ route('residents.edit', $resident) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @endcan
                        @can('delete', $resident)
                            <form method="POST" action="{{ route('residents.destroy', $resident) }}" class="d-inline"
                                  data-confirm="Delete resident {{ $resident->name }}?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No residents registered yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
