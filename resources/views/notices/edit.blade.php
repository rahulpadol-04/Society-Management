@extends('layouts.app')
@section('title', 'Edit Notice')

@section('content')
<div class="row"><div class="col-lg-8">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('notices.update', $notice) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title', $notice->title) }}" class="form-control" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category" id="category" class="form-select">
                        @foreach (['notice' => 'Notice', 'announcement' => 'Announcement', 'circular' => 'Circular', 'event' => 'Event'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('category', $notice->category) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Audience</label>
                    <select name="audience" class="form-select">
                        @foreach (['all' => 'All Residents', 'owners' => 'Owners Only', 'tenants' => 'Tenants Only', 'staff' => 'Staff Only'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('audience', $notice->audience) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input type="hidden" name="pinned" value="0">
                        <input type="checkbox" name="pinned" value="1" id="pinned" class="form-check-input" @checked(old('pinned', $notice->pinned))>
                        <label class="form-check-label" for="pinned"><i class="bi bi-pin-angle me-1"></i> Pinned</label>
                    </div>
                </div>
            </div>

            <div id="event_at_wrapper" class="mb-3" @style(['display:none' => $notice->category !== 'event'])>
                <label class="form-label fw-semibold">Event Date &amp; Time</label>
                <input type="datetime-local" name="event_at"
                       value="{{ old('event_at', $notice->event_at?->format('Y-m-d\TH:i')) }}"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Body <span class="text-danger">*</span></label>
                <textarea name="body" rows="6" class="form-control" required>{{ old('body', $notice->body) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Replace Attachment</label>
                <input type="file" name="attachment" class="form-control" accept="image/*,application/pdf,.doc,.docx">
                @if ($notice->attachment)
                    <div class="form-text">Current: <a href="{{ \Illuminate\Support\Facades\Storage::url($notice->attachment) }}" target="_blank">View file</a></div>
                @endif
            </div>

            <button class="btn btn-primary">Save Changes</button>
            <a href="{{ route('notices.show', $notice) }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</div></div>
@endsection

@push('scripts')
<script>
$(function () {
    function toggleEventAt() {
        $('#event_at_wrapper').toggle($('#category').val() === 'event');
    }
    $('#category').on('change', toggleEventAt);
});
</script>
@endpush
