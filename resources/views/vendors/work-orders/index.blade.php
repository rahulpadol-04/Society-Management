@extends('layouts.app')
@section('title', 'Work Orders')

@section('page-actions')
    <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary"><i class="bi bi-truck"></i> Vendors</a>
@endsection

@section('content')
<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Reference</th><th>Title</th><th>Vendor</th>
                    <th>Priority</th><th>Status</th><th>Amount</th>
                    <th>Scheduled</th><th>Completed</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($workOrders as $wo)
                <tr>
                    <td><a href="{{ route('work-orders.show', $wo) }}" class="fw-semibold">{{ $wo->reference }}</a></td>
                    <td>{{ \Illuminate\Support\Str::limit($wo->title, 40) }}</td>
                    <td>{{ $wo->vendor?->name ?? '—' }}</td>
                    <td>
                        <span class="badge text-bg-{{ $wo->priority === 'critical' ? 'danger' : ($wo->priority === 'high' ? 'warning' : 'light') }} text-capitalize">
                            {{ $wo->priority }}
                        </span>
                    </td>
                    <td>
                        <span class="badge text-bg-{{ $wo->status === 'completed' ? 'success' : ($wo->status === 'cancelled' ? 'secondary' : ($wo->status === 'in_progress' ? 'primary' : 'info')) }} text-capitalize">
                            {{ str_replace('_', ' ', $wo->status) }}
                        </span>
                    </td>
                    <td>{{ money($wo->amount) }}</td>
                    <td class="text-muted small">{{ $wo->scheduled_for?->format('d M Y') ?? '—' }}</td>
                    <td class="text-muted small">{{ $wo->completed_at?->format('d M Y') ?? '—' }}</td>
                    <td class="text-end">
                        @can('update', $wo)
                        <button class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#statusModal{{ $wo->id }}">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        @endcan
                        <a href="{{ route('work-orders.show', $wo) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>

                {{-- Status Update Modal --}}
                @can('update', $wo)
                <div class="modal fade" id="statusModal{{ $wo->id }}" tabindex="-1">
                    <div class="modal-dialog modal-sm">
                        <form method="POST" action="{{ route('work-orders.status', $wo) }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Update Status</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <select name="status" class="form-select">
                                        @foreach (['open', 'assigned', 'in_progress', 'completed', 'cancelled'] as $st)
                                            <option value="{{ $st }}" @selected($wo->status === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="modal-footer py-2">
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endcan
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No work orders yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
