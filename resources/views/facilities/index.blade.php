@extends('layouts.app')
@section('title', 'Facility Booking')

@section('page-actions')
    @can('create', App\Models\Facility::class)
        <a href="{{ route('facilities.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Facility</a>
    @endcan
    <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary ms-2"><i class="bi bi-calendar3"></i> All Bookings</a>
@endsection

@section('content')
{{-- KPI Cards --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body py-2">
            <div class="text-muted small">Today's Bookings</div>
            <div class="h5 mb-0 text-primary">{{ $todayBookings }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body py-2">
            <div class="text-muted small">Pending Approvals</div>
            <div class="h5 mb-0 text-warning">{{ $pendingBookings }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body py-2">
            <div class="text-muted small">Total Bookings</div>
            <div class="h5 mb-0 text-info">{{ $totalBookings }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body py-2">
            <div class="text-muted small">Facilities</div>
            <div class="h5 mb-0 text-success">{{ $facilities->count() }}</div>
        </div></div>
    </div>
</div>

{{-- Facility Usage Chart --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h2 class="h6 mb-3">Facility Usage</h2>
        <div style="height:220px">
            <canvas data-chart="facility-usage" data-type="bar"></canvas>
        </div>
    </div>
</div>

{{-- Facility Cards --}}
<div class="row g-3 mb-4">
    @forelse ($facilities as $facility)
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm {{ $facility->is_active ? '' : 'opacity-50' }}">
                @if ($facility->image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($facility->image) }}" class="card-img-top" style="height:140px;object-fit:cover" alt="{{ $facility->name }}">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:100px">
                        <i class="bi bi-building fs-1 text-muted"></i>
                    </div>
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h3 class="h6 card-title mb-1">{{ $facility->name }}</h3>
                        <span class="badge text-bg-{{ $facility->is_active ? 'success' : 'secondary' }} ms-1">
                            {{ $facility->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="text-muted small mb-2 text-capitalize">{{ str_replace('_', ' ', $facility->type) }}</div>
                    @if ($facility->description)
                        <p class="small text-muted mb-2">{{ \Illuminate\Support\Str::limit($facility->description, 80) }}</p>
                    @endif
                    <div class="small">
                        @if ($facility->capacity)
                            <span class="text-muted"><i class="bi bi-people me-1"></i>Capacity: {{ $facility->capacity }}</span>
                        @endif
                        @if ($facility->charge > 0)
                            <span class="text-muted ms-2"><i class="bi bi-cash me-1"></i>{{ money($facility->charge) }}</span>
                        @else
                            <span class="text-muted ms-2"><i class="bi bi-cash me-1"></i>Free</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex gap-2">
                    @can('book', $facility)
                        <a href="{{ route('facilities.show', $facility) }}" class="btn btn-sm btn-primary flex-fill">
                            <i class="bi bi-calendar-plus"></i> Book
                        </a>
                    @endcan
                    @can('update', $facility)
                        <a href="{{ route('facilities.edit', $facility) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endcan
                    @can('delete', $facility)
                        <form method="POST" action="{{ route('facilities.destroy', $facility) }}" class="d-inline"
                              data-confirm="Delete facility {{ $facility->name }}?">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-5">No facilities added yet.</div>
    @endforelse
</div>

{{-- Recent Bookings Table --}}
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Recent Bookings</span>
        <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Facility</th><th>Booked By</th><th>Date</th><th>Time</th>
                        <th>Guests</th><th>Amount</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($recentBookings as $booking)
                    <tr>
                        <td class="fw-semibold">{{ $booking->facility?->name ?? '—' }}</td>
                        <td>{{ $booking->booker?->name ?? '—' }}</td>
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
                        <td class="text-end">
                            @can('approve', $booking)
                                @if ($booking->status === 'pending')
                                    <form method="POST" action="{{ route('bookings.approve', $booking) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                @endif
                            @endcan
                            @can('cancel', $booking)
                                @if (in_array($booking->status, ['pending', 'approved']))
                                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="d-inline"
                                          data-confirm="Cancel this booking?">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No bookings yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        CommunityOS.renderCharts();
    });
</script>
@endpush
