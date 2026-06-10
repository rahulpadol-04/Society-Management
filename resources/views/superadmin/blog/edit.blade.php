@extends('layouts.app')
@section('title', 'Edit: '.$blog->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
    <li class="breadcrumb-item active">{{ $blog->title }}</li>
@endsection

@section('content')
<div class="row"><div class="col-xl-10">
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="{{ route('blog.update', $blog) }}">
    @csrf @method('PUT')
    @include('superadmin.blog._form')
    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('blog.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
</div></div>
@endsection
