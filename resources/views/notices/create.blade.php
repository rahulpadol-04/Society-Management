@extends('layouts.app')
@section('title', 'New Notice')

@section('content')
<div class="row"><div class="col-lg-8">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('notices.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category" id="category" class="form-select" required>
                        @foreach (['notice' => 'Notice', 'announcement' => 'Announcement', 'circular' => 'Circular', 'event' => 'Event'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('category') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Audience <span class="text-danger">*</span></label>
                    <select name="audience" class="form-select" required>
                        @foreach (['all' => 'All Residents', 'owners' => 'Owners Only', 'tenants' => 'Tenants Only', 'staff' => 'Staff Only'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('audience', 'all') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input type="hidden" name="pinned" value="0">
                        <input type="checkbox" name="pinned" value="1" id="pinned" class="form-check-input" @checked(old('pinned'))>
                        <label class="form-check-label" for="pinned"><i class="bi bi-pin-angle me-1"></i> Pin this notice</label>
                    </div>
                </div>
            </div>

            {{-- Event date (shown only for event category) --}}
            <div id="event_at_wrapper" class="mb-3" style="display:none">
                <label class="form-label fw-semibold">Event Date &amp; Time</label>
                <input type="datetime-local" name="event_at" value="{{ old('event_at') }}" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Body <span class="text-danger">*</span></label>
                <textarea name="body" rows="6" class="form-control @error('body') is-invalid @enderror" required>{{ old('body') }}</textarea>
                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Attachment</label>
                <input type="file" name="attachment" class="form-control" accept="image/*,application/pdf,.doc,.docx">
            </div>

            <hr>

            {{-- Optional Poll builder --}}
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="checkbox" id="addPoll" class="form-check-input" role="switch">
                    <label class="form-check-label fw-semibold" for="addPoll">Attach a Poll</label>
                </div>
            </div>

            <div id="pollBuilder" style="display:none">
                <div class="card bg-light border-0 mb-3"><div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Poll Question</label>
                        <input type="text" name="poll_question" value="{{ old('poll_question') }}" class="form-control" placeholder="What do you think?">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (optional)</label>
                        <input type="text" name="poll_description" value="{{ old('poll_description') }}" class="form-control">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="hidden" name="poll_multiple_choice" value="0">
                                <input type="checkbox" name="poll_multiple_choice" value="1" id="pollMultiple" class="form-check-input" @checked(old('poll_multiple_choice'))>
                                <label class="form-check-label" for="pollMultiple">Allow multiple selections</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Closes at (optional)</label>
                            <input type="datetime-local" name="poll_closes_at" value="{{ old('poll_closes_at') }}" class="form-control">
                        </div>
                    </div>
                    <label class="form-label fw-semibold">Options (minimum 2)</label>
                    <div id="pollOptions">
                        <div class="input-group mb-2">
                            <input type="text" name="poll_options[]" class="form-control" placeholder="Option 1">
                            <button type="button" class="btn btn-outline-danger remove-option"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" name="poll_options[]" class="form-control" placeholder="Option 2">
                            <button type="button" class="btn btn-outline-danger remove-option"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <button type="button" id="addOption" class="btn btn-sm btn-outline-primary mt-1">
                        <i class="bi bi-plus"></i> Add option
                    </button>
                </div></div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Create Notice</button>
                <a href="{{ route('notices.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection

@push('scripts')
<script>
$(function () {
    // Show/hide event_at based on category
    function toggleEventAt() {
        $('#event_at_wrapper').toggle($('#category').val() === 'event');
    }
    toggleEventAt();
    $('#category').on('change', toggleEventAt);

    // Show/hide poll builder
    $('#addPoll').on('change', function () {
        $('#pollBuilder').toggle(this.checked);
    });

    // Add option
    let optionCount = 2;
    $('#addOption').on('click', function () {
        optionCount++;
        $('#pollOptions').append(
            '<div class="input-group mb-2">' +
            '<input type="text" name="poll_options[]" class="form-control" placeholder="Option ' + optionCount + '">' +
            '<button type="button" class="btn btn-outline-danger remove-option"><i class="bi bi-trash"></i></button>' +
            '</div>'
        );
    });

    // Remove option
    $('#pollOptions').on('click', '.remove-option', function () {
        if ($('#pollOptions .input-group').length > 2) {
            $(this).closest('.input-group').remove();
        }
    });
});
</script>
@endpush
