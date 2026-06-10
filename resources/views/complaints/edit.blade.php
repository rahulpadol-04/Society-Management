@extends('layouts.app')
@section('title', 'Edit Complaint')

@section('content')
<div class="row"><div class="col-lg-8">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('complaints.update', $complaint) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" value="{{ old('title', $complaint->title) }}" class="form-control" required>
            </div>
            <div class="row g-3">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <select name="complaint_category_id" class="form-select">
                        <option value="">— Select —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($complaint->complaint_category_id === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        @foreach (['low', 'medium', 'high', 'critical'] as $p)
                            <option value="{{ $p }}" @selected($complaint->priority === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach (['open', 'assigned', 'in_progress', 'resolved', 'closed'] as $s)
                            <option value="{{ $s }}" @selected($complaint->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @can('assign', $complaint)
            <div class="mb-3">
                <label class="form-label">Assign to</label>
                <select name="assigned_to" class="form-select">
                    <option value="">— Unassigned —</option>
                    @foreach ($staff as $member)
                        <option value="{{ $member->id }}" @selected($complaint->assigned_to === $member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            @endcan
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $complaint->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Note (added to timeline)</label>
                <input type="text" name="note" class="form-control" placeholder="Optional note for this change">
            </div>
            <button class="btn btn-primary">Save changes</button>
            <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</div></div>
@endsection
