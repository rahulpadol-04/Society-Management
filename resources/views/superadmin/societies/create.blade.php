@extends('layouts.app')
@section('title', 'Provision New Society')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('societies.index') }}">Societies</a></li>
    <li class="breadcrumb-item active">New Society</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-8">
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="{{ route('societies.store') }}">
    @csrf

    <h2 class="h6 mb-3 border-bottom pb-2">Society Details</h2>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Society Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Contact Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone') }}">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Subscription Plan</label>
            <select name="plan_id" class="form-select @error('plan_id') is-invalid @enderror">
                <option value="">— Free Trial —</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }} ({{ $plan->billing_cycle }})
                    </option>
                @endforeach
            </select>
            @error('plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label">Address</label>
            <input type="text" name="address_line1" class="form-control" value="{{ old('address_line1') }}" placeholder="Street address">
        </div>
        <div class="col-md-4">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" value="{{ old('city') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control" value="{{ old('state') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Postal Code</label>
            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code') }}">
        </div>
    </div>

    <h2 class="h6 mt-4 mb-3 border-bottom pb-2">Society Admin Account</h2>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Admin Name <span class="text-danger">*</span></label>
            <input type="text" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror"
                   value="{{ old('admin_name') }}" required>
            @error('admin_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Admin Email <span class="text-danger">*</span></label>
            <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror"
                   value="{{ old('admin_email') }}" required>
            @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Admin Password <span class="text-danger">*</span></label>
            <input type="password" name="admin_password" class="form-control @error('admin_password') is-invalid @enderror"
                   minlength="8" required>
            @error('admin_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Provision Society</button>
        <a href="{{ route('societies.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
</div></div>
@endsection
