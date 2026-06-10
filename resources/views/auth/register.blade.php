@extends('layouts.auth')
@section('title', 'Register your society')
@section('card-width', '560px')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <h2 class="h6 text-muted text-uppercase mb-3">Society details</h2>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Society / Apartment name</label>
                <input type="text" name="society_name" value="{{ old('society_name') }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">City</label>
                <input type="text" name="city" value="{{ old('city') }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">State</label>
                <input type="text" name="state" value="{{ old('state') }}" class="form-control">
            </div>
            @if ($plans->isNotEmpty())
                <div class="col-12">
                    <label class="form-label">Plan</label>
                    <select name="plan_id" class="form-select">
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} — {{ money($plan->price) }}/{{ $plan->billing_cycle }}@if($plan->trial_days) (incl. {{ $plan->trial_days }}-day trial)@endif</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <h2 class="h6 text-muted text-uppercase mt-4 mb-3">Administrator account</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full name</label>
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
        </div>

        <div class="form-check my-3">
            <input type="checkbox" name="terms" value="1" class="form-check-input" id="terms" required>
            <label class="form-check-label" for="terms">I agree to the Terms of Service & Privacy Policy</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Create society account</button>
    </form>

    <p class="text-center text-muted small mt-4 mb-0">
        Already registered? <a href="{{ route('login') }}">Sign in</a>
    </p>
@endsection
