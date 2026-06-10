@extends('layouts.app')
@section('title', 'Helpdesk')

@section('page-actions')
    @can('create', App\Models\SupportTicket::class)
        <a href="{{ route('helpdesk.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Ticket</a>
    @endcan
    @can('update', new App\Models\SupportTicket)
        <a href="{{ route('helpdesk.escalation.index') }}" class="btn btn-outline-secondary ms-1"><i class="bi bi-diagram-3"></i> Escalation Matrix</a>
    @endcan
@endsection

@section('content')
{{-- KPI stat-cards --}}
<div class="row g-3 mb-4">
    @php
        $kpis = [
            'open'        => ['color' => 'warning',  'icon' => 'bi-ticket-perforated', 'label' => 'Open'],
            'in_progress' => ['color' => 'primary',  'icon' => 'bi-arrow-repeat',       'label' => 'In Progress'],
            'on_hold'     => ['color' => 'info',     'icon' => 'bi-pause-circle',       'label' => 'On Hold'],
            'resolved'    => ['color' => 'success',  'icon' => 'bi-check-circle',       'label' => 'Resolved'],
            'closed'      => ['color' => 'dark',     'icon' => 'bi-x-circle',           'label' => 'Closed'],
        ];
    @endphp
    @foreach ($kpis as $status => $meta)
        <div class="col-6 col-md">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-soft-{{ $meta['color'] }}"><i class="bi {{ $meta['icon'] }}"></i></span>
                    <div class="min-w-0">
                        <div class="stat-value text-truncate">{{ $statusCounts[$status] ?? 0 }}</div>
                        <div class="stat-label">{{ $meta['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Tickets table --}}
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assignee</th>
                        <th>SLA</th>
                        <th>Raised</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td>
                            <a href="{{ route('helpdesk.show', $ticket) }}" class="fw-semibold">{{ $ticket->ticket_number }}</a>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($ticket->subject, 45) }}</td>
                        <td class="text-capitalize">{{ str_replace('_', ' ', $ticket->category) }}</td>
                        <td>
                            @php
                                $pBadge = match($ticket->priority) {
                                    'urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', default => 'secondary'
                                };
                            @endphp
                            <span class="badge text-bg-{{ $pBadge }} text-capitalize">{{ $ticket->priority }}</span>
                        </td>
                        <td>
                            <span class="badge status-{{ $ticket->status }} text-capitalize">{{ str_replace('_', ' ', $ticket->status) }}</span>
                        </td>
                        <td>{{ $ticket->assignee?->name ?? '—' }}</td>
                        <td>
                            @if ($ticket->isOverdue())
                                <span class="badge text-bg-danger">Overdue</span>
                            @elseif ($ticket->sla_due_at)
                                <span class="text-muted small">{{ $ticket->sla_due_at->diffForHumans() }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $ticket->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('helpdesk.show', $ticket) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            @can('delete', $ticket)
                                <form method="POST" action="{{ route('helpdesk.destroy', $ticket) }}" class="d-inline" data-confirm="Delete this ticket?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No support tickets yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
