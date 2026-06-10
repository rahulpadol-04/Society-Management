@extends('layouts.app')
@section('title', 'Staff Leaves')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('society-staff.index') }}">Staff</a></li>
    <li class="breadcrumb-item active">Leaves</li>
@endsection

@section('page-actions')
    @can('create', App\Models\StaffLeave::class)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
            <i class="bi bi-calendar-plus"></i> Apply Leave
        </button>
    @endcan
@endsection

@section('content')

{{-- Leaves DataTable --}}
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($leaves as $leave)
                    <tr>
                        <td class="fw-semibold">{{ $leave->staffMember->name }}</td>
                        <td class="text-capitalize">{{ $leave->type }}</td>
                        <td>{{ $leave->from_date->format('d M Y') }}</td>
                        <td>{{ $leave->to_date->format('d M Y') }}</td>
                        <td>{{ $leave->days }}</td>
                        <td class="text-muted small">{{ Str::limit($leave->reason ?? '—', 40) }}</td>
                        <td>
                            <span class="badge status-{{ $leave->status }} text-capitalize">
                                {{ $leave->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if ($leave->status === 'pending')
                                @can('update', $leave)
                                    <form method="POST" action="{{ route('staff.leaves.approve', $leave) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('staff.leaves.reject', $leave) }}" class="d-inline ms-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No leave requests found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Apply Leave Modal --}}
@can('create', App\Models\StaffLeave::class)
<div class="modal fade" id="applyLeaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('staff.leaves.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Staff Member <span class="text-danger">*</span></label>
                        <select name="staff_member_id" class="form-select" required>
                            <option value="">— Select Staff —</option>
                            @foreach ($staffList as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Leave Type</label>
                        <select name="type" class="form-select" required>
                            @foreach (['casual','sick','paid','unpaid'] as $t)
                                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date" name="from_date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date" name="to_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason</label>
                        <textarea name="reason" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Leave</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
