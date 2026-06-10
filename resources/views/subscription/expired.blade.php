@extends('layouts.app')
@section('title', 'Subscription Expired')

@section('content')
<div class="card shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-exclamation-triangle text-warning" style="font-size:3rem"></i>
        <h2 class="h4 mt-3">Your subscription has expired</h2>
        <p class="text-muted">Renew your plan to restore access to all CommunityOS modules for your society.</p>
        @if (\Route::has('billing.index'))
            <a href="{{ route('billing.index') }}" class="btn btn-primary mt-2">Renew now</a>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf
            <button class="btn btn-link text-muted">Sign out</button>
        </form>
    </div>
</div>
@endsection
