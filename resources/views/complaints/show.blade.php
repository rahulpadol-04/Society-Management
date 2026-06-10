@extends('layouts.app')
@section('title', 'Complaint '.$complaint->reference)

@section('page-actions')
    @can('update', $complaint)
        <a href="{{ route('complaints.edit', $complaint) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    @endcan
    @can('delete', $complaint)
        <form method="POST" action="{{ route('complaints.destroy', $complaint) }}" class="d-inline" data-confirm="Delete this complaint?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3"><div class="card-body">
            <div class="d-flex justify-content-between">
                <h2 class="h5">{{ $complaint->title }}</h2>
                <span class="badge status-{{ $complaint->status }} text-capitalize">{{ str_replace('_', ' ', $complaint->status) }}</span>
            </div>
            <p class="text-muted">{{ $complaint->description ?: 'No description provided.' }}</p>
            <div class="row small text-muted">
                <div class="col-sm-4"><strong>Reference:</strong> {{ $complaint->reference }}</div>
                <div class="col-sm-4"><strong>Category:</strong> {{ $complaint->category?->name ?? '—' }}</div>
                <div class="col-sm-4"><strong>Priority:</strong> {{ ucfirst($complaint->priority) }}</div>
                <div class="col-sm-4 mt-1"><strong>Raised by:</strong> {{ $complaint->raisedBy?->name }}</div>
                <div class="col-sm-4 mt-1"><strong>Assignee:</strong> {{ $complaint->assignee?->name ?? 'Unassigned' }}</div>
                <div class="col-sm-4 mt-1"><strong>SLA due:</strong> {{ $complaint->sla_due_at?->format('d M Y H:i') ?? '—' }}</div>
            </div>

            @if ($complaint->attachments)
                <hr><strong class="small">Attachments</strong>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    @foreach ($complaint->attachments as $path)
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($path) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-paperclip"></i> File</a>
                    @endforeach
                </div>
            @endif
        </div></div>

        @can('feedback', $complaint)
        @if (in_array($complaint->status, ['resolved', 'closed']) && ! $complaint->feedback)
            <div class="card shadow-sm mb-3"><div class="card-body">
                <h3 class="h6">Rate the resolution</h3>
                <form method="POST" action="{{ route('complaints.feedback', $complaint) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-auto">
                        <select name="rating" class="form-select">
                            @for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }} ★</option>@endfor
                        </select>
                    </div>
                    <div class="col"><input type="text" name="comment" class="form-control" placeholder="Comment (optional)"></div>
                    <div class="col-auto"><button class="btn btn-primary">Submit</button></div>
                </form>
            </div></div>
        @endif
        @endcan
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-body">
            <h3 class="h6 mb-3">Timeline</h3>
            <ul class="list-unstyled timeline mb-0">
                @forelse ($complaint->activities as $activity)
                    <li class="mb-3">
                        <div class="small fw-semibold text-capitalize">{{ str_replace('_', ' ', $activity->action) }}</div>
                        @if ($activity->to_status)
                            <div class="small text-muted">{{ $activity->from_status }} → {{ $activity->to_status }}</div>
                        @endif
                        @if ($activity->note)<div class="small">{{ $activity->note }}</div>@endif
                        <div class="text-muted" style="font-size:.75rem">{{ $activity->user?->name }} · {{ $activity->created_at->diffForHumans() }}</div>
                    </li>
                @empty
                    <li class="text-muted small">No activity recorded.</li>
                @endforelse
            </ul>
        </div></div>
    </div>
</div>
@endsection
