@extends('layouts.app')
@section('title', 'Staff Management')

@section('page-actions')
    @can('create', App\Models\StaffMember::class)
        <a href="{{ route('society-staff.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Add Staff
        </a>
    @endcan
    @can('attendance', App\Models\StaffMember::class)
        <a href="{{ route('staff.attendance.index') }}" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-calendar-check"></i> Attendance
        </a>
    @endcan
    @can('payroll', App\Models\StaffMember::class)
        <a href="{{ route('staff.payroll.index') }}" class="btn btn-outline-info ms-2">
            <i class="bi bi-cash-coin"></i> Payroll
        </a>
    @endcan
    <a href="{{ route('staff.shifts.index') }}" class="btn btn-outline-secondary ms-2">
        <i class="bi bi-clock"></i> Shifts
    </a>
@endsection

@section('content')

{{-- KPI Stat Cards --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-soft-primary"><i class="bi bi-person-badge"></i></span>
                <div class="min-w-0">
                    <div class="stat-value text-truncate">{{ $counts['total'] }}</div>
                    <div class="stat-label">Total Staff</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-soft-success"><i class="bi bi-check-circle"></i></span>
                <div class="min-w-0">
                    <div class="stat-value text-truncate">{{ $presentToday }}</div>
                    <div class="stat-label">Present Today</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-soft-warning"><i class="bi bi-calendar-x"></i></span>
                <div class="min-w-0">
                    <div class="stat-value text-truncate">{{ $onLeave }}</div>
                    <div class="stat-label">On Leave</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-soft-info"><i class="bi bi-grid-3x3-gap"></i></span>
                <div class="min-w-0">
                    <div class="stat-value text-truncate">{{ count($departments) }}</div>
                    <div class="stat-label">Departments</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Staff DataTable --}}
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Shift</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($staff as $member)
                    <tr>
                        <td>
                            <a href="{{ route('society-staff.show', $member) }}" class="fw-semibold">
                                {{ $member->name }}
                            </a>
                            @if ($member->phone)
                                <div class="small text-muted">{{ $member->phone }}</div>
                            @endif
                        </td>
                        <td>{{ $member->employee_code ?? '—' }}</td>
                        <td class="text-capitalize">{{ str_replace('_', ' ', $member->department) }}</td>
                        <td>{{ $member->designation ?? '—' }}</td>
                        <td class="text-capitalize">{{ $member->shift ?? '—' }}</td>
                        <td>{{ money($member->salary) }}</td>
                        <td>
                            <span class="badge status-{{ $member->status }} text-capitalize">
                                {{ str_replace('_', ' ', $member->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('society-staff.show', $member) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('update', $member)
                                <a href="{{ route('society-staff.edit', $member) }}" class="btn btn-sm btn-outline-primary ms-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endcan
                            @can('delete', $member)
                                <form method="POST" action="{{ route('society-staff.destroy', $member) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1"
                                            data-confirm="Remove {{ $member->name }} from staff?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No staff members found.
                            @can('create', App\Models\StaffMember::class)
                                <a href="{{ route('society-staff.create') }}">Add the first staff member</a>.
                            @endcan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
