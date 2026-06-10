@extends('layouts.app')
@section('title', $staff->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('society-staff.index') }}">Staff</a></li>
    <li class="breadcrumb-item active">{{ $staff->name }}</li>
@endsection

@section('page-actions')
    @can('update', $staff)
        <a href="{{ route('society-staff.edit', $staff) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    {{-- Profile Card --}}
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                @if ($staff->photo)
                    <img src="{{ asset('storage/'.$staff->photo) }}" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3"
                         style="width:100px;height:100px;">
                        <i class="bi bi-person-fill fs-1 text-white"></i>
                    </div>
                @endif
                <h5 class="mb-1">{{ $staff->name }}</h5>
                <p class="text-muted small mb-2">{{ $staff->designation ?? 'Staff Member' }}</p>
                <span class="badge status-{{ $staff->status }} text-capitalize">
                    {{ str_replace('_', ' ', $staff->status) }}
                </span>

                <hr>

                <table class="table table-sm text-start">
                    <tr>
                        <td class="text-muted small">Employee Code</td>
                        <td class="fw-semibold">{{ $staff->employee_code ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Department</td>
                        <td class="text-capitalize">{{ str_replace('_', ' ', $staff->department) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Shift</td>
                        <td class="text-capitalize">{{ $staff->shift ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Phone</td>
                        <td>{{ $staff->phone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Email</td>
                        <td>{{ $staff->email ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Joining Date</td>
                        <td>{{ $staff->joining_date?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Monthly Salary</td>
                        <td>{{ money($staff->salary) }}</td>
                    </tr>
                    @if ($staff->address)
                        <tr>
                            <td class="text-muted small">Address</td>
                            <td>{{ $staff->address }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="col-lg-8">
        <ul class="nav nav-tabs mb-3" id="staffTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#attendanceTab">Attendance</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#leavesTab">Leaves</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payrollTab">Payroll</button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- Attendance Tab --}}
            <div class="tab-pane fade show active" id="attendanceTab">
                <div class="card shadow-sm">
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($staff->attendances as $att)
                                    <tr>
                                        <td>{{ $att->date->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge status-{{ $att->status }} text-capitalize">
                                                {{ str_replace('_', ' ', $att->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $att->check_in ?? '—' }}</td>
                                        <td>{{ $att->check_out ?? '—' }}</td>
                                        <td>{{ $att->hours ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No attendance records.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Leaves Tab --}}
            <div class="tab-pane fade" id="leavesTab">
                <div class="card shadow-sm">
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($staff->leaves as $leave)
                                    <tr>
                                        <td class="text-capitalize">{{ $leave->type }}</td>
                                        <td>{{ $leave->from_date->format('d M Y') }}</td>
                                        <td>{{ $leave->to_date->format('d M Y') }}</td>
                                        <td>{{ $leave->days }}</td>
                                        <td>
                                            <span class="badge status-{{ $leave->status }} text-capitalize">
                                                {{ $leave->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No leave records.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payroll Tab --}}
            <div class="tab-pane fade" id="payrollTab">
                <div class="card shadow-sm">
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Period</th>
                                        <th>Basic</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Net</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($staff->payrolls as $payroll)
                                    <tr>
                                        <td>{{ $payroll->period }}</td>
                                        <td>{{ money($payroll->basic) }}</td>
                                        <td>{{ money($payroll->allowances) }}</td>
                                        <td>{{ money($payroll->deductions) }}</td>
                                        <td class="fw-semibold">{{ money($payroll->net) }}</td>
                                        <td>
                                            <span class="badge status-{{ $payroll->status }} text-capitalize">
                                                {{ $payroll->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No payroll records.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
