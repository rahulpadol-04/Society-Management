@extends('layouts.app')
@section('title', 'Complaints')

@section('page-actions')
    @can('create', App\Models\Complaint::class)
        <a href="{{ route('complaints.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Complaint</a>
    @endcan
@endsection

@section('content')
<div class="row g-2 mb-3">
    @foreach (['open' => 'warning', 'assigned' => 'info', 'in_progress' => 'primary', 'resolved' => 'success', 'closed' => 'secondary'] as $status => $color)
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm"><div class="card-body py-2">
                <div class="text-muted small text-capitalize">{{ str_replace('_', ' ', $status) }}</div>
                <div class="h5 mb-0 text-{{ $color }}">{{ $statusCounts[$status] ?? 0 }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Reference</th><th>Title</th><th>Category</th><th>Priority</th>
                    <th>Status</th><th>Assignee</th><th>SLA</th><th>Raised</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($complaints as $complaint)
                <tr>
                    <td><a href="{{ route('complaints.show', $complaint) }}" class="fw-semibold">{{ $complaint->reference }}</a></td>
                    <td>{{ \Illuminate\Support\Str::limit($complaint->title, 40) }}</td>
                    <td>{{ $complaint->category?->name ?? '—' }}</td>
                    <td><span class="badge text-bg-light text-capitalize">{{ $complaint->priority }}</span></td>
                    <td><span class="badge status-{{ $complaint->status }} text-capitalize">{{ str_replace('_', ' ', $complaint->status) }}</span></td>
                    <td>{{ $complaint->assignee?->name ?? 'Unassigned' }}</td>
                    <td>
                        @if ($complaint->isOverdue())
                            <span class="badge text-bg-danger">Overdue</span>
                        @elseif ($complaint->sla_due_at)
                            <span class="text-muted small">{{ $complaint->sla_due_at->diffForHumans() }}</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $complaint->created_at->format('d M Y') }}</td>
                    <td class="text-end"><a href="{{ route('complaints.show', $complaint) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No complaints yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
