@extends('layouts.app')
@section('title', 'My Bookings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('facilities.index') }}">Facilities</a></li>
    <li class="breadcrumb-item active">Bookings</li>
@endsection

@section('page-actions')
    <a href="{{ route('facilities.index') }}" class="btn btn-outline-secondary"><i class="bi bi-building"></i> Facilities</a>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Facility</th>
                        <th>Booked By</th>
                        <th>Flat</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Guests</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($bookings as $booking)
                    <tr>
                        <td class="text-muted small">{{ $booking->id }}</td>
                        <td>
                            <a href="{{ route('facilities.show', $booking->facility_id) }}" class="fw-semibold">
                                {{ $booking->facility?->name ?? '—' }}
                            </a>
                            <div class="text-muted small text-capitalize">{{ $booking->facility?->type }}</div>
                        </td>
                        <td>{{ $booking->booker?->name ?? '—' }}</td>
                        <td class="small text-muted">{{ $booking->flat?->number ?? '—' }}</td>
                        <td>{{ $booking->booking_date?->format('d M Y') }}</td>
                        <td class="small">{{ $booking->start_time }} — {{ $booking->end_time }}</td>
                        <td>{{ $booking->guests }}</td>
                        <td>{{ $booking->amount > 0 ? money($booking->amount) : 'Free' }}</td>
                        <td>
                            <span class="badge text-bg-{{ match($booking->status) {
                                'approved'  => 'success',
                                'pending'   => 'warning',
                                'rejected'  => 'danger',
                                'cancelled' => 'secondary',
                                'completed' => 'info',
                                default     => 'light',
                            } }} text-capitalize">{{ $booking->status }}</span>
                        </td>
                        <td>
                            @can('approve', $booking)
                                @if ($booking->status === 'pending')
                                    <form method="POST" action="{{ route('bookings.approve', $booking) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('bookings.reject', $booking) }}" class="d-inline"
                                          data-confirm="Reject booking #{{ $booking->id }}?">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Reject</button>
                                    </form>
                                @endif
                            @endcan
                            @can('cancel', $booking)
                                @if (in_array($booking->status, ['pending', 'approved']))
                                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="d-inline"
                                          data-confirm="Cancel booking #{{ $booking->id }}?">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary">Cancel</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No bookings found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
