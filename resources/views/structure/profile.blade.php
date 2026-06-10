@extends('layouts.app')
@section('title', 'Society Profile')

@section('content')
<div class="row"><div class="col-lg-9">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('society-profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Society Name</label>
                    <input name="name" value="{{ old('name', $society->name) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Registration No.</label>
                    <input name="registration_number" value="{{ old('registration_number', $society->registration_number) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $society->email) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input name="phone" value="{{ old('phone', $society->phone) }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Address Line 1</label>
                    <input name="address_line1" value="{{ old('address_line1', $society->address_line1) }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Address Line 2</label>
                    <input name="address_line2" value="{{ old('address_line2', $society->address_line2) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input name="city" value="{{ old('city', $society->city) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input name="state" value="{{ old('state', $society->state) }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Postal Code</label>
                    <input name="postal_code" value="{{ old('postal_code', $society->postal_code) }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Country</label>
                    <input name="country" value="{{ old('country', $society->country) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    @if ($society->logo)<img src="{{ \Illuminate\Support\Facades\Storage::url($society->logo) }}" class="mt-2" height="48" alt="logo">@endif
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary">Save Profile</button>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
