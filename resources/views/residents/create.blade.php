@extends('layouts.app')
@section('title', 'Add Resident')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('residents.index') }}">Residents</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-9">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('residents.store') }}" enctype="multipart/form-data">
            @include('residents._form')
            <hr>
            <button class="btn btn-primary">Save Resident</button>
            <a href="{{ route('residents.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</div></div>
@endsection
