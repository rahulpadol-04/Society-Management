@extends('layouts.app')
@section('title', 'Messages / Inbox')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('communication.index') }}">Communication</a></li>
    <li class="breadcrumb-item active">Inbox</li>
@endsection

@section('page-actions')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newConversationModal">
        <i class="bi bi-plus-lg"></i> New Message
    </button>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body p-0">
        @forelse ($conversations as $conv)
            <a href="{{ route('communication.messages.show', $conv) }}"
               class="d-flex align-items-start gap-3 p-3 border-bottom text-decoration-none text-body hover-bg">
                <span class="stat-icon bg-soft-primary flex-shrink-0" style="width:42px;height:42px;">
                    <i class="bi bi-chat-dots"></i>
                </span>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold text-truncate">
                            {{ $conv->subject ?: 'Conversation #'.$conv->id }}
                        </span>
                        <span class="text-muted small flex-shrink-0 ms-2">
                            {{ $conv->last_message_at?->diffForHumans() ?? $conv->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="text-muted small">
                        {{ $conv->participants->pluck('participant.name')->filter()->implode(', ') }}
                    </div>
                    <span class="badge {{ $conv->type === 'group' ? 'text-bg-info' : 'text-bg-secondary' }} small">
                        {{ ucfirst($conv->type) }}
                    </span>
                </div>
            </a>
        @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-chat fs-2 d-block mb-2"></i>
                No conversations yet.
            </div>
        @endforelse
    </div>
</div>

{{-- New conversation modal --}}
<div class="modal fade" id="newConversationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('communication.messages.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Optional subject">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                        <textarea name="body" rows="4" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
