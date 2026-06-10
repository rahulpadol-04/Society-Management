@extends('layouts.app')
@section('title', 'Edit: '.$page->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cms.index') }}">CMS Pages</a></li>
    <li class="breadcrumb-item active">{{ $page->title }}</li>
@endsection

@section('content')
<div class="row"><div class="col-xl-10">
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="{{ route('cms.update', $page) }}">
    @csrf @method('PUT')
    @include('superadmin.cms._form')
    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('cms.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
</div></div>
@endsection
