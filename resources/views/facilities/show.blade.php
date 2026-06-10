@extends('layouts.app')
@section('title', $facility->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('facilities.index') }}">Facilities</a></li>
    <li class="breadcrumb-item active">{{ $facility->name }}</li>
@endsection

@section('page-actions')
    @can('update', $facility)
        <a href="{{ route('facilities.edit', $facility) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    @endcan
    @can('delete', $facility)
        <form method="POST" action="{{ route('facilities.destroy', $facility) }}" class="d-inline"
              data-confirm="Delete facility {{ $facility->name }}?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger ms-1"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-5">
        {{-- Facility Details --}}
        <div class="card shadow-sm mb-3">
            @if ($facility->image)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($facility->image) }}"
                     class="card-img-top" style="max-height:200px;object-fit:cover" alt="{{ $facility->name }}">
            @endif
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h2 class="h5 mb-0">{{ $facility->name }}</h2>
                    <span class="badge text-bg-{{ $facility->is_active ? 'success' : 'secondary' }}">
                        {{ $facility->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-muted small text-capitalize mb-3">{{ str_replace('_', ' ', $facility->type) }}</p>
                @if ($facility->description)
                    <p class="mb-3">{{ $facility->description }}</p>
                @endif
                <table class="table table-sm table-borderless small mb-0">
                    <tr>
                        <td class="text-muted fw-semibold">Capacity</td>
                        <td>{{ $facility->capacity ? $facility->capacity.' persons' : 'Unlimited' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Charge</td>
                        <td>{{ $facility->charge > 0 ? money($facility->charge).' / booking' : 'Free' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Approval</td>
                        <td>{{ $facility->requires_approval ? 'Required' : 'Auto-approved' }}</td>
                    </tr>
                    @if ($facility->opening_time)
                    <tr>
                        <td class="text-muted fw-semibold">Hours</td>
                        <td>{{ $facility->opening_time }} — {{ $facility->closing_time }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted fw-semibold">Slot</td>
                        <td>{{ $facility->slot_minutes }} minutes</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Booking Form --}}
        @can('book', $facility)
        @if ($facility->is_active)
        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-calendar-plus me-1"></i> Book this Facility</div>
            <div class="card-body">
                <form method="POST" action="{{ route('facilities.book', $facility) }}">
                    @csrf
                    <input type="hidden" name="facility_id" value="{{ $facility->id }}">

                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="booking_date" value="{{ old('booking_date', now()->addDay()->format('Y-m-d')) }}"
                               class="form-control @error('booking_date') is-invalid @enderror"
                               min="{{ now()->format('Y-m-d') }}" required>
                        @error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}"
                                   class="form-control @error('start_time') is-invalid @enderror" required>
                            @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" value="{{ old('end_time', '10:00') }}"
                                   class="form-control @error('end_time') is-invalid @enderror" required>
                            @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Guests</label>
                        <input type="number" name="guests" value="{{ old('guests', 0) }}"
                               class="form-control" min="0"
                               @if($facility->capacity) max="{{ $facility->capacity }}" @endif>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Optional notes for this booking">{{ old('notes') }}</textarea>
                    </div>

                    @if ($facility->charge > 0)
                        <div class="alert alert-info small py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            A charge of {{ money($facility->charge) }} will apply for this booking.
                        </div>
                    @endif

                    @if ($facility->requires_approval)
                        <div class="alert alert-warning small py-2">
                            <i class="bi bi-clock-history me-1"></i>
                            This booking requires admin approval before it is confirmed.
                        </div>
                    @endif

                    <button class="btn btn-primary w-100">Submit Booking</button>
                </form>
            </div>
        </div>
        @endif
        @endcan
    </div>

    <div class="col-lg-7">
        {{-- Bookings Table --}}
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Bookings</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead>
                            <tr>
                                <th>Date</th><th>Time</th><th>Booked By</th>
                                <th>Guests</th><th>Status</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($facility->bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_date?->format('d M Y') }}</td>
                                <td class="small">{{ $booking->start_time }} — {{ $booking->end_time }}</td>
                                <td>{{ $booking->booker?->name ?? '—' }}</td>
                                <td>{{ $booking->guests }}</td>
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
                                <td class="text-end">
                                    @can('approve', $booking)
                                        @if ($booking->status === 'pending')
                                            <form method="POST" action="{{ route('bookings.approve', $booking) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('bookings.reject', $booking) }}" class="d-inline"
                                                  data-confirm="Reject this booking?">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">Reject</button>
                                            </form>
                                        @endif
                                    @endcan
                                    @can('cancel', $booking)
                                        @if (in_array($booking->status, ['pending', 'approved']))
                                            <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="d-inline"
                                                  data-confirm="Cancel this booking?">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-secondary">Cancel</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No bookings for this facility.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
