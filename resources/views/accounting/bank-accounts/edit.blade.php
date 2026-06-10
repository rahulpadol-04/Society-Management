@extends('layouts.app')
@section('title', 'Edit Bank Account — '.$bank->name)

@section('content')
<div class="row"><div class="col-lg-7">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('accounting.bank.update', $bank) }}">
            @csrf @method('PUT')
            @include('accounting.bank-accounts._form', ['account' => $bank])
            <div class="mt-3">
                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('accounting.bank.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
