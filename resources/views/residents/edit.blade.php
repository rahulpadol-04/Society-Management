@extends('layouts.app')
@section('title', 'Edit Resident')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('residents.index') }}">Residents</a></li>
    <li class="breadcrumb-item"><a href="{{ route('residents.show', $resident) }}">{{ $resident->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-9">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('residents.update', $resident) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('residents._form')
            <hr>
            <button class="btn btn-primary">Update Resident</button>
            <a href="{{ route('residents.show', $resident) }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</div></div>
@endsection
