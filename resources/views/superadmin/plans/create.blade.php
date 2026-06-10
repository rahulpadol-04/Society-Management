@extends('layouts.app')
@section('title', 'New Subscription Plan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('plans.index') }}">Plans</a></li>
    <li class="breadcrumb-item active">New Plan</li>
@endsection

@section('content')
<div class="row"><div class="col-xl-9">
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="{{ route('plans.store') }}">
    @csrf
    @include('superadmin.plans._form', ['plan' => null])
    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Create Plan</button>
        <a href="{{ route('plans.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
</div></div>
@endsection
