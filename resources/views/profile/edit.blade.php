@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Profile information</h2>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="text-center mb-3">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle" width="90" height="90" alt="avatar">
                </div>
                <div class="mb-3"><label class="form-label">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Email</label>
                    <input type="email" value="{{ $user->email }}" class="form-control" disabled></div>
                <div class="mb-3"><label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Designation</label>
                    <input type="text" name="designation" value="{{ old('designation', $user->designation) }}" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Avatar</label>
                    <input type="file" name="avatar" class="form-control" accept="image/*"></div>
                <button class="btn btn-primary">Save changes</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Change password</h2>
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf @method('PUT')
                <div class="mb-3"><label class="form-label">Current password</label>
                    <input type="password" name="current_password" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">New password</label>
                    <input type="password" name="password" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Confirm new password</label>
                    <input type="password" name="password_confirmation" class="form-control" required></div>
                <button class="btn btn-primary">Update password</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
