@extends('layouts.app')
@section('title', 'Edit Ticket '.$ticket->ticket_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('helpdesk.index') }}">Helpdesk</a></li>
    <li class="breadcrumb-item"><a href="{{ route('helpdesk.show', $ticket) }}">{{ $ticket->ticket_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('helpdesk.update', $ticket) }}">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject', $ticket->subject) }}"
                               class="form-control @error('subject') is-invalid @enderror" required>
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category" class="form-select">
                                @foreach (['general', 'technical', 'billing', 'facility', 'security', 'account', 'other'] as $cat)
                                    <option value="{{ $cat }}" @selected(old('category', $ticket->category) === $cat)>{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Priority</label>
                            <select name="priority" class="form-select">
                                @foreach (['low', 'medium', 'high', 'urgent'] as $p)
                                    <option value="{{ $p }}" @selected(old('priority', $ticket->priority) === $p)>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                @foreach (['open', 'in_progress', 'on_hold', 'resolved', 'closed'] as $s)
                                    <option value="{{ $s }}" @selected(old('status', $ticket->status) === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @can('assign', App\Models\SupportTicket::class)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">— Unassigned —</option>
                            @foreach ($staff as $member)
                                <option value="{{ $member->id }}" @selected(old('assigned_to', $ticket->assigned_to) == $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endcan

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="5" class="form-control">{{ old('description', $ticket->description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Change Note (timeline)</label>
                        <input type="text" name="note" class="form-control" placeholder="Optional note describing this change">
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('helpdesk.show', $ticket) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
