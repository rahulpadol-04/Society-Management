@extends('layouts.app')
@section('title', 'Edit Plan: '.$plan->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('plans.index') }}">Plans</a></li>
    <li class="breadcrumb-item active">{{ $plan->name }}</li>
@endsection

@section('content')
<div class="row"><div class="col-xl-9">
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="{{ route('plans.update', $plan) }}">
    @csrf @method('PUT')
    @include('superadmin.plans._form')
    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('plans.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
</div></div>
@endsection
