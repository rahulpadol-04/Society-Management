@extends('layouts.app')
@section('title', 'New Complaint')

@section('content')
<div class="row"><div class="col-lg-8">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
            </div>
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <select name="complaint_category_id" class="form-select">
                        <option value="">— Select —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        @foreach (['low', 'medium', 'high', 'critical'] as $p)
                            <option value="{{ $p }}" @selected($p === 'medium')>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Attachments</label>
                <input type="file" name="attachments[]" class="form-control" multiple accept="image/*,application/pdf">
            </div>
            <button class="btn btn-primary">Submit complaint</button>
            <a href="{{ route('complaints.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</div></div>
@endsection
