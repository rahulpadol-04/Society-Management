@extends('layouts.app')
@section('title', 'Edit Facility')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('facilities.index') }}">Facilities</a></li>
    <li class="breadcrumb-item"><a href="{{ route('facilities.show', $facility) }}">{{ $facility->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-8">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('facilities.update', $facility) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $facility->name) }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        @foreach (['clubhouse', 'gym', 'pool', 'court', 'hall', 'other'] as $t)
                            <option value="{{ $t }}" @selected(old('type', $facility->type) === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $facility->description) }}</textarea>
            </div>

            <div class="row g-3">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Capacity</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $facility->capacity) }}"
                           class="form-control" min="1" placeholder="Unlimited if blank">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Charge per Booking</label>
                    <input type="number" name="charge" value="{{ old('charge', $facility->charge) }}"
                           class="form-control" min="0" step="0.01">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Slot Duration (minutes)</label>
                    <input type="number" name="slot_minutes" value="{{ old('slot_minutes', $facility->slot_minutes) }}"
                           class="form-control" min="15">
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Opening Time</label>
                    <input type="time" name="opening_time" value="{{ old('opening_time', $facility->opening_time) }}" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Closing Time</label>
                    <input type="time" name="closing_time" value="{{ old('closing_time', $facility->closing_time) }}" class="form-control">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="requires_approval" value="1"
                               id="requiresApproval" @checked(old('requires_approval', $facility->requires_approval))>
                        <label class="form-check-label" for="requiresApproval">Requires Admin Approval</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               id="isActive" @checked(old('is_active', $facility->is_active))>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Image</label>
                @if ($facility->image)
                    <div class="mb-2">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($facility->image) }}" alt="Current image"
                             style="max-height:80px;border-radius:4px">
                    </div>
                @endif
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <button class="btn btn-primary">Save Changes</button>
            <a href="{{ route('facilities.show', $facility) }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</div></div>
@endsection
