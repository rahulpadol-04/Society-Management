@extends('layouts.app')
@section('title', 'Parking Slots')

@section('page-actions')
    @can('create', App\Models\ParkingSlot::class)
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSlot"><i class="bi bi-plus-lg"></i> Add Slot</button>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead><tr><th>Code</th><th>Type</th><th>Location</th><th>Assigned Unit</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($slots as $slot)
                <tr>
                    <td class="fw-semibold">{{ $slot->code }}</td>
                    <td class="text-capitalize">{{ $slot->type }}</td>
                    <td>{{ $slot->location ?? '—' }}</td>
                    <td>{{ $slot->flat?->number ?? '—' }}</td>
                    <td><span class="badge text-bg-{{ $slot->status === 'available' ? 'success' : ($slot->status === 'assigned' ? 'primary' : 'secondary') }} text-capitalize">{{ $slot->status }}</span></td>
                    <td class="text-end">
                        @can('delete', $slot)
                        <form method="POST" action="{{ route('parking.destroy', $slot) }}" class="d-inline" data-confirm="Remove this slot?">
                            @csrf @method('DELETE') <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No parking slots defined.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>

@can('create', App\Models\ParkingSlot::class)
<div class="modal fade" id="addSlot" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('parking.store') }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Add Parking Slot</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Code</label><input name="code" class="form-control" required placeholder="P-001"></div>
            <div class="mb-2"><label class="form-label">Type</label>
                <select name="type" class="form-select">
                    @foreach (['car', 'bike', 'visitor', 'ev', 'handicap'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
                </select>
            </div>
            <div class="mb-2"><label class="form-label">Location</label><input name="location" class="form-control" placeholder="Basement 1"></div>
            <div class="mb-2"><label class="form-label">Assign to Unit</label>
                <select name="flat_id" class="form-select">
                    <option value="">— Unassigned —</option>
                    @foreach ($flats as $flat)<option value="{{ $flat->id }}">{{ $flat->number }}</option>@endforeach
                </select>
            </div>
            <input type="hidden" name="status" value="available">
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Add Slot</button></div>
    </form>
</div></div></div>
@endcan
@endsection
