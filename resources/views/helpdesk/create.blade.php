@extends('layouts.app')
@section('title', 'New Support Ticket')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('helpdesk.index') }}">Helpdesk</a></li>
    <li class="breadcrumb-item active">New Ticket</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('helpdesk.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" value="{{ old('subject') }}"
                               class="form-control @error('subject') is-invalid @enderror"
                               placeholder="Brief description of your issue" required>
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror">
                                @foreach (['general', 'technical', 'billing', 'facility', 'security', 'account', 'other'] as $cat)
                                    <option value="{{ $cat }}" @selected(old('category', 'general') === $cat)>{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Priority</label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror">
                                @foreach (['low', 'medium', 'high', 'urgent'] as $p)
                                    <option value="{{ $p }}" @selected(old('priority', 'medium') === $p)>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="5"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Provide as much detail as possible…">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="bi bi-send"></i> Submit Ticket</button>
                        <a href="{{ route('helpdesk.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-2"><i class="bi bi-info-circle text-info"></i> Tips</h6>
                <ul class="small text-muted ps-3 mb-0">
                    <li>Choose the correct category to ensure fast routing.</li>
                    <li>Mark as <strong>Urgent</strong> only for critical issues.</li>
                    <li>You'll receive email updates when your ticket is actioned.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
