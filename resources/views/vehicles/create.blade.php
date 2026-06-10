@extends('layouts.app')
@section('title', 'Register Vehicle')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
    <li class="breadcrumb-item active">Register</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-9">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('vehicles.store') }}">
            @include('vehicles._form')
            <hr>
            <button class="btn btn-primary">Register Vehicle</button>
            <a href="{{ route('vehicles.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</div></div>
@endsection
