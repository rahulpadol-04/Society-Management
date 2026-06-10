@extends('layouts.app')
@section('title', 'Staff Shifts')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('society-staff.index') }}">Staff</a></li>
    <li class="breadcrumb-item active">Shifts</li>
@endsection

@section('page-actions')
    @can('create', App\Models\StaffShift::class)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createShiftModal">
            <i class="bi bi-plus-lg"></i> Add Shift
        </button>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Description</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($shifts as $shift)
                    <tr>
                        <td class="fw-semibold">{{ $shift->name }}</td>
                        <td>{{ $shift->start_time }}</td>
                        <td>{{ $shift->end_time }}</td>
                        <td class="text-muted small">{{ $shift->description ?? '—' }}</td>
                        <td class="text-end">
                            @can('update', $shift)
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#editShiftModal{{ $shift->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            @endcan
                            @can('delete', $shift)
                                <form method="POST" action="{{ route('staff.shifts.destroy', $shift) }}" class="d-inline ms-1">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            data-confirm="Delete shift {{ $shift->name }}?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>

                    {{-- Edit Modal per shift --}}
                    @can('update', $shift)
                    <div class="modal fade" id="editShiftModal{{ $shift->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Shift</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('staff.shifts.update', $shift) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Name</label>
                                            <input type="text" name="name" value="{{ $shift->name }}" class="form-control" required>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">Start Time</label>
                                                <input type="time" name="start_time" value="{{ $shift->start_time }}" class="form-control" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">End Time</label>
                                                <input type="time" name="end_time" value="{{ $shift->end_time }}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $shift->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Shift</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No shifts defined yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Create Shift Modal --}}
@can('create', App\Models\StaffShift::class)
<div class="modal fade" id="createShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('staff.shifts.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Morning">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
