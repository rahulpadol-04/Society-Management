@extends('layouts.app')
@section('title', 'Vehicles')

@section('page-actions')
    @can('create', App\Models\Vehicle::class)
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Register Vehicle</a>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Registration</th><th>Type</th><th>Make / Model</th><th>Color</th>
                    <th>Resident</th><th>Flat</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($vehicles as $vehicle)
                <tr>
                    <td><a href="{{ route('vehicles.show', $vehicle) }}" class="fw-semibold">{{ $vehicle->registration_number }}</a></td>
                    <td><span class="badge text-bg-light text-capitalize">{{ $vehicle->type }}</span></td>
                    <td>{{ trim(($vehicle->make ?? '').' '.($vehicle->model ?? '')) ?: '—' }}</td>
                    <td>{{ $vehicle->color ?? '—' }}</td>
                    <td>{{ $vehicle->resident?->name ?? '—' }}</td>
                    <td>{{ $vehicle->flat?->number ?? '—' }}</td>
                    <td>
                        <span class="badge text-bg-{{ $vehicle->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($vehicle->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        @can('update', $vehicle)
                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @endcan
                        @can('delete', $vehicle)
                            <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" class="d-inline"
                                  data-confirm="Delete vehicle {{ $vehicle->registration_number }}?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No vehicles registered.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
