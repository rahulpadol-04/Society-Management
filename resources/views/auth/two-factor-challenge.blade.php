@extends('layouts.auth')
@section('title', 'Two-factor authentication')

@section('content')
    <p class="text-muted small">Enter the 6-digit code from your authenticator app, or a recovery code.</p>
    <form method="POST" action="{{ route('two-factor.challenge.verify') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Authentication code</label>
            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror" required autofocus>
            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Verify</button>
    </form>
    <p class="text-center small mt-3 mb-0"><a href="{{ route('login') }}">Back to sign in</a></p>
@endsection
