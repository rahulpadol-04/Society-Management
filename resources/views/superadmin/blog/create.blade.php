@extends('layouts.app')
@section('title', 'New Blog Post')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
    <li class="breadcrumb-item active">New Post</li>
@endsection

@section('content')
<div class="row"><div class="col-xl-10">
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="{{ route('blog.store') }}">
    @csrf
    @include('superadmin.blog._form', ['blog' => null])
    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Create Post</button>
        <a href="{{ route('blog.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
</div></div>
@endsection
