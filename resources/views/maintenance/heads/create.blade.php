@extends('layouts.app')
@section('title', 'New Maintenance Head')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('maintenance.index') }}">Maintenance Billing</a></li>
    <li class="breadcrumb-item"><a href="{{ route('maintenance.heads.index') }}">Charge Heads</a></li>
    <li class="breadcrumb-item active">New</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-8">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('maintenance.heads.store') }}">
            @csrf
            @include('maintenance.heads._form')
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary">Create Head</button>
                <a href="{{ route('maintenance.heads.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
