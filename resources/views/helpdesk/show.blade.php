@extends('layouts.app')
@section('title', 'Ticket '.$ticket->ticket_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('helpdesk.index') }}">Helpdesk</a></li>
    <li class="breadcrumb-item active">{{ $ticket->ticket_number }}</li>
@endsection

@section('page-actions')
    @can('update', $ticket)
        <a href="{{ route('helpdesk.edit', $ticket) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    @endcan
    @can('escalate', App\Models\SupportTicket::class)
        @if (!in_array($ticket->status, ['resolved', 'closed']))
            <button class="btn btn-outline-warning ms-1" data-bs-toggle="modal" data-bs-target="#escalateModal">
                <i class="bi bi-arrow-up-circle"></i> Escalate
            </button>
        @endif
    @endcan
    @can('close', App\Models\SupportTicket::class)
        @if (!in_array($ticket->status, ['closed']))
            <button class="btn btn-outline-danger ms-1" data-bs-toggle="modal" data-bs-target="#closeModal">
                <i class="bi bi-x-circle"></i> Close
            </button>
        @endif
    @endcan
    @can('delete', $ticket)
        <form method="POST" action="{{ route('helpdesk.destroy', $ticket) }}" class="d-inline ms-1" data-confirm="Delete this ticket permanently?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    {{-- Main detail --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <h2 class="h5 mb-0">{{ $ticket->subject }}</h2>
                    <span class="badge status-{{ $ticket->status }} text-capitalize">{{ str_replace('_', ' ', $ticket->status) }}</span>
                </div>

                @if ($ticket->description)
                    <p class="text-muted mb-3">{{ $ticket->description }}</p>
                @endif

                <div class="row small text-muted g-2">
                    <div class="col-sm-4"><strong>Ticket #:</strong> {{ $ticket->ticket_number }}</div>
                    <div class="col-sm-4"><strong>Category:</strong> <span class="text-capitalize">{{ str_replace('_', ' ', $ticket->category) }}</span></div>
                    <div class="col-sm-4">
                        <strong>Priority:</strong>
                        @php $pBadge = match($ticket->priority) { 'urgent'=>'danger','high'=>'warning','medium'=>'info',default=>'secondary' }; @endphp
                        <span class="badge text-bg-{{ $pBadge }} text-capitalize">{{ $ticket->priority }}</span>
                    </div>
                    <div class="col-sm-4"><strong>Raised by:</strong> {{ $ticket->raisedBy?->name ?? '—' }}</div>
                    <div class="col-sm-4"><strong>Assignee:</strong> {{ $ticket->assignee?->name ?? 'Unassigned' }}</div>
                    <div class="col-sm-4">
                        <strong>SLA due:</strong>
                        @if ($ticket->sla_due_at)
                            {{ $ticket->sla_due_at->format('d M Y H:i') }}
                            @if ($ticket->isOverdue()) <span class="badge text-bg-danger ms-1">Overdue</span> @endif
                        @else
                            —
                        @endif
                    </div>
                    @if ($ticket->escalation_level > 0)
                        <div class="col-sm-4"><strong>Escalation:</strong> Level {{ $ticket->escalation_level }}</div>
                    @endif
                    @if ($ticket->resolved_at)
                        <div class="col-sm-4"><strong>Resolved:</strong> {{ $ticket->resolved_at->format('d M Y H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Assign panel (staff only) --}}
        @can('assign', App\Models\SupportTicket::class)
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h3 class="h6 mb-3"><i class="bi bi-person-check"></i> Assign Ticket</h3>
                <form method="POST" action="{{ route('helpdesk.assign', $ticket) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label small">Assign to</label>
                        <select name="assigned_to" class="form-select form-select-sm">
                            <option value="">— Select Staff —</option>
                            @foreach ($staff as $member)
                                <option value="{{ $member->id }}" @selected($ticket->assigned_to == $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">Note (optional)</label>
                        <input type="text" name="note" class="form-control form-control-sm" placeholder="Assignment note">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-primary">Assign</button>
                    </div>
                </form>
            </div>
        </div>
        @endcan

        {{-- Replies thread --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0"><i class="bi bi-chat-left-dots"></i> Replies</h3>
                <span class="badge text-bg-secondary">{{ $ticket->replies->count() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse ($ticket->replies as $reply)
                    <div class="p-3 border-bottom {{ $reply->is_internal ? 'bg-warning-subtle' : '' }}">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold small">{{ $reply->author?->name ?? 'System' }}</span>
                            <div class="d-flex align-items-center gap-2">
                                @if ($reply->is_internal)
                                    <span class="badge text-bg-warning text-dark">Internal Note</span>
                                @endif
                                <span class="text-muted" style="font-size:.75rem">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <p class="mb-0 mt-1 small">{{ $reply->message }}</p>
                        @if ($reply->attachment)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($reply->attachment) }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1"><i class="bi bi-paperclip"></i> Attachment</a>
                        @endif
                    </div>
                @empty
                    <div class="p-3 text-muted small">No replies yet.</div>
                @endforelse
            </div>

            @if (!in_array($ticket->status, ['closed']))
            <div class="card-footer bg-transparent">
                <form method="POST" action="{{ route('helpdesk.reply', $ticket) }}">
                    @csrf
                    <div class="mb-2">
                        <textarea name="message" rows="3" class="form-control"
                                  placeholder="Write your reply…" required></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        @can('update', $ticket)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="is_internal" id="is_internal" value="1">
                            <label class="form-check-label small" for="is_internal">Internal note (staff only)</label>
                        </div>
                        @else
                            <div></div>
                        @endcan
                        <button class="btn btn-sm btn-primary"><i class="bi bi-send"></i> Reply</button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>

    {{-- Sidebar: timeline --}}
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="h6 mb-0"><i class="bi bi-clock-history"></i> Timeline</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled timeline mb-0">
                    @forelse ($ticket->activities as $activity)
                        <li class="mb-3">
                            <div class="small fw-semibold text-capitalize">{{ str_replace('_', ' ', $activity->action) }}</div>
                            @if ($activity->to_status)
                                <div class="small text-muted">
                                    <span class="badge status-{{ $activity->from_status }}">{{ str_replace('_',' ',$activity->from_status) }}</span>
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <span class="badge status-{{ $activity->to_status }}">{{ str_replace('_',' ',$activity->to_status) }}</span>
                                </div>
                            @endif
                            @if ($activity->note)
                                <div class="small">{{ $activity->note }}</div>
                            @endif
                            <div class="text-muted" style="font-size:.75rem">
                                {{ $activity->user?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}
                            </div>
                        </li>
                    @empty
                        <li class="text-muted small">No activity recorded.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Escalate Modal --}}
@can('escalate', App\Models\SupportTicket::class)
<div class="modal fade" id="escalateModal" tabindex="-1" aria-labelledby="escalateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('helpdesk.escalate', $ticket) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="escalateModalLabel">Escalate Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Current escalation level: <strong>{{ $ticket->escalation_level }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">Reason for escalation</label>
                        <textarea name="note" rows="3" class="form-control" placeholder="Describe why this is being escalated…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-warning"><i class="bi bi-arrow-up-circle"></i> Escalate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- Close Modal --}}
@can('close', App\Models\SupportTicket::class)
<div class="modal fade" id="closeModal" tabindex="-1" aria-labelledby="closeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('helpdesk.close', $ticket) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="closeModalLabel">Close Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Closing note (optional)</label>
                        <textarea name="note" rows="3" class="form-control" placeholder="Resolution summary or reason for closing…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger"><i class="bi bi-x-circle"></i> Close Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
