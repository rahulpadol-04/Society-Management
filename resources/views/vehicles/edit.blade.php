@extends('layouts.app')
@section('title', 'Edit Vehicle')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vehicles.show', $vehicle) }}">{{ $vehicle->registration_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-9">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
            @method('PUT')
            @include('vehicles._form')
            <hr>
            <button class="btn btn-primary">Update Vehicle</button>
            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</div></div>
@endsection
