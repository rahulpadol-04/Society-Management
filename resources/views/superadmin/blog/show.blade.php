@extends('layouts.app')
@section('title', $blog->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
    <li class="breadcrumb-item active">{{ $blog->title }}</li>
@endsection

@section('page-actions')
    <div class="d-flex gap-2">
        @can('publish', $blog)
            <form method="POST" action="{{ route('blog.publish', $blog) }}">
                @csrf
                <button class="btn {{ $blog->status === 'published' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                    <i class="bi {{ $blog->status === 'published' ? 'bi-eye-slash' : 'bi-globe' }}"></i>
                    {{ $blog->status === 'published' ? 'Unpublish' : 'Publish' }}
                </button>
            </form>
        @endcan
        @can('update', $blog)
            <a href="{{ route('blog.edit', $blog) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="row"><div class="col-lg-9">
<div class="card shadow-sm"><div class="card-body">
    <div class="d-flex justify-content-between align-items-start mb-1">
        <h1 class="h4 mb-0">{{ $blog->title }}</h1>
        <span class="badge {{ $blog->status === 'published' ? 'status-active' : 'text-bg-secondary' }} text-capitalize">
            {{ $blog->status }}
        </span>
    </div>
    <div class="text-muted small mb-3">
        By {{ $blog->author?->name ?? 'Unknown' }}
        @if ($blog->category) &nbsp;·&nbsp; {{ $blog->category }} @endif
        @if ($blog->published_at) &nbsp;·&nbsp; {{ $blog->published_at->format('d M Y') }} @endif
        &nbsp;·&nbsp; {{ number_format($blog->views) }} views
    </div>
    @if ($blog->excerpt)
        <p class="lead small">{{ $blog->excerpt }}</p>
    @endif
    @if ($blog->content)
        <div class="mt-3">{!! nl2br(e($blog->content)) !!}</div>
    @else
        <p class="text-muted">No content yet.</p>
    @endif
</div></div>
</div></div>
@endsection
