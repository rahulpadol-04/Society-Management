@extends('layouts.app')
@section('title', 'Staff Attendance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('society-staff.index') }}">Staff</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')

{{-- Date Picker --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('staff.attendance.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Date</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Load
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Attendance Grid --}}
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('staff.attendance.store') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Staff Member</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($staff as $member)
                        @php $att = $member->attendances->first(); @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $member->name }}</div>
                                <div class="small text-muted">{{ $member->employee_code ?? '' }}</div>
                            </td>
                            <td class="text-capitalize">{{ str_replace('_', ' ', $member->department) }}</td>
                            <td>
                                <div class="d-flex gap-3">
                                    @foreach (['present','absent','half_day'] as $s)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="attendance[{{ $member->id }}][status]"
                                                   id="att_{{ $member->id }}_{{ $s }}"
                                                   value="{{ $s }}"
                                                   @checked(($att->status ?? 'present') === $s)>
                                            <label class="form-check-label text-capitalize" for="att_{{ $member->id }}_{{ $s }}">
                                                {{ str_replace('_', ' ', $s) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <input type="time" name="attendance[{{ $member->id }}][check_in]"
                                       value="{{ $att->check_in ?? '' }}"
                                       class="form-control form-control-sm" style="width:130px;">
                            </td>
                            <td>
                                <input type="time" name="attendance[{{ $member->id }}][check_out]"
                                       value="{{ $att->check_out ?? '' }}"
                                       class="form-control form-control-sm" style="width:130px;">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No active staff members found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if ($staff->isNotEmpty())
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Save Attendance
                    </button>
                    <a href="{{ route('society-staff.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
