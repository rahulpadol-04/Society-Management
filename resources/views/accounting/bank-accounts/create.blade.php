@extends('layouts.app')
@section('title', 'New Bank Account')

@section('content')
<div class="row"><div class="col-lg-7">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('accounting.bank.store') }}">
            @csrf
            @include('accounting.bank-accounts._form')
            <div class="mt-3">
                <button class="btn btn-primary">Create Account</button>
                <a href="{{ route('accounting.bank.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
