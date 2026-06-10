@extends('layouts.app')
@section('title', $conversation->subject ?: 'Conversation #'.$conversation->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('communication.index') }}">Communication</a></li>
    <li class="breadcrumb-item"><a href="{{ route('communication.messages.index') }}">Inbox</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($conversation->subject ?? 'Conversation #'.$conversation->id, 30) }}</li>
@endsection

@section('content')
<div class="row g-3">
    {{-- Thread --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $conversation->subject ?: 'Conversation #'.$conversation->id }}</span>
                <span class="badge {{ $conversation->type === 'group' ? 'text-bg-info' : 'text-bg-secondary' }}">
                    {{ ucfirst($conversation->type) }}
                </span>
            </div>
            <div class="card-body" style="max-height:500px;overflow-y:auto;" id="messageThread">
                @forelse ($conversation->messages as $msg)
                    @php $isMine = $msg->user_id === auth()->id(); @endphp
                    <div class="d-flex {{ $isMine ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                        <div style="max-width:75%;">
                            <div class="small text-muted mb-1 {{ $isMine ? 'text-end' : '' }}">
                                {{ $msg->author?->name ?? 'System' }} &middot;
                                {{ $msg->created_at->format('d M Y H:i') }}
                            </div>
                            <div class="p-2 rounded {{ $isMine ? 'bg-primary text-white' : 'bg-body-secondary' }}">
                                {{ $msg->body }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">No messages in this conversation.</p>
                @endforelse
            </div>
        </div>

        {{-- Reply form --}}
        <div class="card shadow-sm"><div class="card-body">
            <form method="POST" action="{{ route('communication.messages.store') }}">
                @csrf
                <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
                <div class="mb-2">
                    <textarea name="body" rows="3" class="form-control"
                              placeholder="Write your reply…" required></textarea>
                </div>
                <button class="btn btn-primary btn-sm"><i class="bi bi-send me-1"></i>Send Reply</button>
            </form>
        </div></div>
    </div>

    {{-- Participants sidebar --}}
    <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-body">
            <h3 class="h6 mb-3">Participants</h3>
            <ul class="list-unstyled mb-0">
                @foreach ($conversation->participants as $part)
                    <li class="d-flex align-items-center gap-2 mb-2">
                        <span class="stat-icon bg-soft-secondary" style="width:32px;height:32px;font-size:.7rem;">
                            <i class="bi bi-person"></i>
                        </span>
                        <span class="small">{{ $part->participant?->name ?? '(deleted user)' }}</span>
                    </li>
                @endforeach
            </ul>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Scroll to bottom of thread on load.
$(function () {
    var t = document.getElementById('messageThread');
    if (t) { t.scrollTop = t.scrollHeight; }
});
</script>
@endpush
