@extends('layouts.app')
@section('title', $notice->title)

@section('page-actions')
    @can('publish', $notice)
        @if (! $notice->is_published)
            <form method="POST" action="{{ route('notices.publish', $notice) }}" class="d-inline">
                @csrf
                <button class="btn btn-success"><i class="bi bi-megaphone"></i> Publish</button>
            </form>
        @endif
    @endcan
    @can('update', $notice)
        <a href="{{ route('notices.edit', $notice) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    @endcan
    @can('delete', $notice)
        <form method="POST" action="{{ route('notices.destroy', $notice) }}" class="d-inline"
              data-confirm="Delete this notice permanently?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    {{-- Main content --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                {{-- Header --}}
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    @php
                        $categoryColors = [
                            'notice'       => 'primary',
                            'announcement' => 'success',
                            'circular'     => 'info',
                            'event'        => 'warning',
                        ];
                        $color = $categoryColors[$notice->category] ?? 'secondary';
                    @endphp
                    <span class="badge text-bg-{{ $color }} text-capitalize">{{ $notice->category }}</span>
                    @if ($notice->pinned)
                        <span class="badge text-bg-warning"><i class="bi bi-pin-angle-fill"></i> Pinned</span>
                    @endif
                    @if (! $notice->is_published)
                        <span class="badge text-bg-secondary">Draft</span>
                    @endif
                    <span class="text-muted small ms-auto">
                        {{ $notice->author?->name ?? 'Admin' }} &middot;
                        {{ ($notice->published_at ?? $notice->created_at)->format('d M Y') }}
                    </span>
                </div>

                <h1 class="h4 mb-3">{{ $notice->title }}</h1>

                @if ($notice->category === 'event' && $notice->event_at)
                    <div class="alert alert-warning d-flex align-items-center gap-2 py-2">
                        <i class="bi bi-calendar-event fs-5"></i>
                        <div><strong>Event Date:</strong> {{ $notice->event_at->format('l, d F Y \a\t H:i') }}</div>
                    </div>
                @endif

                <div class="notice-body">
                    {!! nl2br(e($notice->body)) !!}
                </div>

                @if ($notice->attachment)
                    <hr>
                    <div>
                        <strong class="small">Attachment</strong><br>
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($notice->attachment) }}"
                           target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
                            <i class="bi bi-paperclip"></i> Download / View
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Poll widget --}}
        @if ($notice->poll)
            @php
                $poll        = $notice->poll;
                $totalVotes  = $poll->totalVotes();
                $hasVoted    = ! empty($userVotedOptionIds);
                $pollClosed  = ! $poll->is_active || ($poll->closes_at && $poll->closes_at->isPast());
            @endphp
            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-bar-chart me-1"></i> Poll</span>
                    @if ($pollClosed)
                        <span class="badge text-bg-secondary">Closed</span>
                    @elseif ($poll->closes_at)
                        <span class="text-muted small">Closes {{ $poll->closes_at->diffForHumans() }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="fw-semibold mb-3">{{ $poll->question }}</p>
                    @if ($poll->description)
                        <p class="text-muted small">{{ $poll->description }}</p>
                    @endif

                    {{-- Show results if voted or poll is closed --}}
                    @if ($hasVoted || $pollClosed)
                        <div class="mb-3">
                            @foreach ($poll->options as $option)
                                @php
                                    $pct = $totalVotes > 0 ? round(($option->votes_count / $totalVotes) * 100) : 0;
                                    $isMyVote = in_array($option->id, $userVotedOptionIds, true);
                                @endphp
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>{{ $option->label }} @if($isMyVote)<i class="bi bi-check-circle-fill text-success ms-1"></i>@endif</span>
                                        <span class="text-muted">{{ $option->votes_count }} ({{ $pct }}%)</span>
                                    </div>
                                    <div class="progress" style="height:8px">
                                        <div class="progress-bar {{ $isMyVote ? 'bg-success' : 'bg-primary' }}"
                                             role="progressbar" style="width:{{ $pct }}%"
                                             aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            @endforeach
                            <p class="text-muted small mt-2">{{ $totalVotes }} total {{ Str::plural('vote', $totalVotes) }}</p>
                        </div>
                        @if ($hasVoted && ! $pollClosed)
                            <p class="text-success small"><i class="bi bi-check-circle-fill"></i> You have voted.</p>
                        @endif
                    @else
                        {{-- Vote form --}}
                        @can('vote', $poll)
                            <form method="POST" action="{{ route('notices.poll.vote', $poll) }}">
                                @csrf
                                @foreach ($poll->options as $option)
                                    <div class="form-check mb-2">
                                        @if ($poll->multiple_choice)
                                            <input type="checkbox" name="option_ids[]" value="{{ $option->id }}"
                                                   id="opt{{ $option->id }}" class="form-check-input">
                                        @else
                                            <input type="radio" name="option_ids[]" value="{{ $option->id }}"
                                                   id="opt{{ $option->id }}" class="form-check-input">
                                        @endif
                                        <label class="form-check-label" for="opt{{ $option->id }}">{{ $option->label }}</label>
                                    </div>
                                @endforeach
                                <button class="btn btn-primary btn-sm mt-2">Submit Vote</button>
                            </form>
                        @else
                            <p class="text-muted small">You do not have permission to vote on this poll.</p>
                        @endcan
                    @endif

                    {{-- Admin: close poll --}}
                    @can('close', $poll)
                        @if ($poll->is_active && ! ($poll->closes_at && $poll->closes_at->isPast()))
                            <hr>
                            <form method="POST" action="{{ route('notices.poll.close', $poll) }}" class="d-inline"
                                  data-confirm="Close this poll?">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">Close Poll</button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        @endif

        {{-- Admin: create poll if none exists --}}
        @can('manage', App\Models\Poll::class)
            @if (! $notice->poll)
                <div class="card shadow-sm">
                    <div class="card-header"><span class="fw-semibold"><i class="bi bi-bar-chart me-1"></i> Attach a Poll</span></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('notices.polls.store', $notice) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Question</label>
                                <input type="text" name="question" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description (optional)</label>
                                <input type="text" name="description" class="form-control">
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="hidden" name="multiple_choice" value="0">
                                        <input type="checkbox" name="multiple_choice" value="1" id="mc" class="form-check-input">
                                        <label class="form-check-label" for="mc">Multiple choice</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Closes at</label>
                                    <input type="datetime-local" name="closes_at" class="form-control">
                                </div>
                            </div>
                            <label class="form-label fw-semibold">Options (min 2)</label>
                            <div id="inlinePollOptions">
                                <div class="input-group mb-2">
                                    <input type="text" name="options[]" class="form-control" placeholder="Option 1" required>
                                    <button type="button" class="btn btn-outline-danger remove-option"><i class="bi bi-trash"></i></button>
                                </div>
                                <div class="input-group mb-2">
                                    <input type="text" name="options[]" class="form-control" placeholder="Option 2" required>
                                    <button type="button" class="btn btn-outline-danger remove-option"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                            <button type="button" id="addInlineOption" class="btn btn-sm btn-outline-primary mb-3">
                                <i class="bi bi-plus"></i> Add option
                            </button>
                            <br>
                            <button class="btn btn-primary">Attach Poll</button>
                        </form>
                    </div>
                </div>
            @endif
        @endcan
    </div>

    {{-- Sidebar meta --}}
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="h6 mb-3">Details</h3>
                <dl class="row small mb-0">
                    <dt class="col-5">Category</dt>
                    <dd class="col-7 text-capitalize">{{ $notice->category }}</dd>
                    <dt class="col-5">Audience</dt>
                    <dd class="col-7 text-capitalize">{{ $notice->audience }}</dd>
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">{{ $notice->is_published ? 'Published' : 'Draft' }}</dd>
                    @if ($notice->published_at)
                        <dt class="col-5">Published</dt>
                        <dd class="col-7">{{ $notice->published_at->format('d M Y') }}</dd>
                    @endif
                    <dt class="col-5">Author</dt>
                    <dd class="col-7">{{ $notice->author?->name ?? 'Admin' }}</dd>
                    @if ($notice->pinned)
                        <dt class="col-5">Pinned</dt>
                        <dd class="col-7"><i class="bi bi-pin-angle-fill text-warning"></i> Yes</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    let inlineCount = 2;
    $('#addInlineOption').on('click', function () {
        inlineCount++;
        $('#inlinePollOptions').append(
            '<div class="input-group mb-2">' +
            '<input type="text" name="options[]" class="form-control" placeholder="Option ' + inlineCount + '">' +
            '<button type="button" class="btn btn-outline-danger remove-option"><i class="bi bi-trash"></i></button>' +
            '</div>'
        );
    });
    $('#inlinePollOptions').on('click', '.remove-option', function () {
        if ($('#inlinePollOptions .input-group').length > 2) {
            $(this).closest('.input-group').remove();
        }
    });
});
</script>
@endpush
