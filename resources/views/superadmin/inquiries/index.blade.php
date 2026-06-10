@extends('layouts.app')
@section('title', 'Contact Inquiries')

@section('content')

{{-- Status filter --}}
<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="{{ route('inquiries.index') }}"
       class="btn btn-sm {{ ! request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
        All ({{ $counts->sum() }})
    </a>
    @foreach (['new','in_progress','responded','closed'] as $st)
        <a href="{{ route('inquiries.index', ['status' => $st]) }}"
           class="btn btn-sm {{ request('status') === $st ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ ucfirst(str_replace('_', ' ', $st)) }} ({{ $counts[$st] ?? 0 }})
        </a>
    @endforeach
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Society</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($inquiries as $inq)
                    <tr>
                        <td class="fw-semibold">{{ $inq->name }}</td>
                        <td class="small text-muted">{{ $inq->email }}</td>
                        <td>{{ $inq->subject ?? '—' }}</td>
                        <td>{{ $inq->society_name ?? '—' }}</td>
                        <td>
                            @php
                                $badgeMap = [
                                    'new' => 'status-trial',
                                    'in_progress' => 'text-bg-info',
                                    'responded' => 'status-active',
                                    'closed' => 'text-bg-secondary',
                                ];
                            @endphp
                            <span class="badge {{ $badgeMap[$inq->status] ?? 'text-bg-secondary' }} text-capitalize">
                                {{ str_replace('_', ' ', $inq->status) }}
                            </span>
                        </td>
                        <td class="small">{{ $inq->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('inquiries.show', $inq) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('delete', $inq)
                                    <form method="POST" action="{{ route('inquiries.destroy', $inq) }}"
                                          data-confirm="Delete this inquiry?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No inquiries found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $inquiries->links() }}
    </div>
</div>
@endsection
