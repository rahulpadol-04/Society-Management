@extends('layouts.app')
@section('title', 'Edit Society: '.$society->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('societies.index') }}">Societies</a></li>
    <li class="breadcrumb-item"><a href="{{ route('societies.show', $society) }}">{{ $society->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-8">
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="{{ route('societies.update', $society) }}">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Society Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $society->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $society->email) }}">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $society->phone) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                @foreach (['active', 'suspended', 'pending'] as $st)
                    <option value="{{ $st }}" {{ old('status', $society->status) === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Subscription Plan</label>
            <select name="plan_id" class="form-select">
                <option value="">— None —</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" {{ old('plan_id', $society->current_plan_id) == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" value="{{ old('city', $society->city) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control" value="{{ old('state', $society->state) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Country</label>
            <input type="text" name="country" class="form-control" value="{{ old('country', $society->country) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Postal Code</label>
            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $society->postal_code) }}">
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('societies.show', $society) }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
</div></div>
@endsection
