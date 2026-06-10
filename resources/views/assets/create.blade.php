@extends('layouts.app')
@section('title', 'Add Asset')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Assets</a></li>
    <li class="breadcrumb-item active">Add Asset</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-10">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data">
            @csrf
            @include('assets._form')
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Create Asset</button>
                <a href="{{ route('assets.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
