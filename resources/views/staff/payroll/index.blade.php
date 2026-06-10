@extends('layouts.app')
@section('title', 'Staff Payroll')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('society-staff.index') }}">Staff</a></li>
    <li class="breadcrumb-item active">Payroll</li>
@endsection

@section('content')

{{-- Period Selector + Generate --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <form method="GET" action="{{ route('staff.payroll.index') }}" class="d-flex gap-2">
                    <input type="month" name="period" value="{{ $period }}" class="form-control">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
            @can('payroll', App\Models\StaffMember::class)
                <div class="col-auto">
                    <form method="POST" action="{{ route('staff.payroll.generate') }}">
                        @csrf
                        <input type="hidden" name="period" value="{{ $period }}">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-lightning"></i> Generate Payroll for {{ $period }}
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</div>

{{-- Payroll DataTable --}}
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Department</th>
                        <th>Period</th>
                        <th>Basic</th>
                        <th>Allowances</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($payrolls as $payroll)
                    <tr>
                        <td class="fw-semibold">{{ $payroll->staffMember->name }}</td>
                        <td class="text-capitalize">{{ str_replace('_', ' ', $payroll->staffMember->department) }}</td>
                        <td>{{ $payroll->period }}</td>
                        <td>{{ money($payroll->basic) }}</td>
                        <td>{{ money($payroll->allowances) }}</td>
                        <td>{{ money($payroll->deductions) }}</td>
                        <td class="fw-semibold text-success">{{ money($payroll->net) }}</td>
                        <td>{{ $payroll->days_present }}</td>
                        <td>{{ $payroll->days_absent }}</td>
                        <td>
                            <span class="badge status-{{ $payroll->status }} text-capitalize">
                                {{ $payroll->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if ($payroll->status !== 'paid')
                                @can('payroll', App\Models\StaffMember::class)
                                    <form method="POST" action="{{ route('staff.payroll.pay', $payroll) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"
                                                data-confirm="Mark payroll for {{ $payroll->staffMember->name }} as paid?">
                                            <i class="bi bi-check-circle"></i> Mark Paid
                                        </button>
                                    </form>
                                @endcan
                            @else
                                <span class="text-muted small">{{ $payroll->paid_at?->format('d M Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            No payroll records for {{ $period }}.
                            @can('payroll', App\Models\StaffMember::class)
                                Use the Generate button above to create payroll.
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
