@extends('layouts.app')
@section('title', 'Edit Asset')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Assets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('assets.show', $asset) }}">{{ $asset->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-10">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('assets.update', $asset) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('assets._form')
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Update Asset</button>
                <a href="{{ route('assets.show', $asset) }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
