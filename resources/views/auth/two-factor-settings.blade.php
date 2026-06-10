@extends('layouts.app')
@section('title', 'Two-Factor Authentication')

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                @if ($user->hasTwoFactorEnabled())
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-shield-check text-success fs-3 me-2"></i>
                        <div>
                            <h2 class="h5 mb-0">Two-factor authentication is enabled</h2>
                            <small class="text-muted">Your account is protected with an authenticator app.</small>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('two-factor.disable') }}">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger">Disable two-factor authentication</button>
                    </form>
                @elseif ($secret)
                    <h2 class="h5">Scan this QR code</h2>
                    <p class="text-muted small">Use Google Authenticator, Authy or any TOTP app, then enter the generated code to confirm.</p>
                    <div class="my-3">{!! $qr !!}</div>
                    <p class="small">Manual key: <code>{{ $secret }}</code></p>
                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-auto">
                            <label class="form-label">Confirmation code</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="col-auto"><button class="btn btn-primary">Confirm & enable</button></div>
                    </form>
                @else
                    <h2 class="h5">Add an extra layer of security</h2>
                    <p class="text-muted">Require a one-time code from an authenticator app when signing in.</p>
                    <form method="POST" action="{{ route('two-factor.enable') }}">
                        @csrf
                        <button class="btn btn-primary">Enable two-factor authentication</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
