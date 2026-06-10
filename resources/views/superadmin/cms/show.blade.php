@extends('layouts.app')
@section('title', $page->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cms.index') }}">CMS Pages</a></li>
    <li class="breadcrumb-item active">{{ $page->title }}</li>
@endsection

@section('page-actions')
    <div class="d-flex gap-2">
        @can('publish', $page)
            <form method="POST" action="{{ route('cms.publish', $page) }}">
                @csrf
                <button class="btn {{ $page->status === 'published' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                    <i class="bi {{ $page->status === 'published' ? 'bi-eye-slash' : 'bi-globe' }}"></i>
                    {{ $page->status === 'published' ? 'Unpublish' : 'Publish' }}
                </button>
            </form>
        @endcan
        @can('update', $page)
            <a href="{{ route('cms.edit', $page) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="row"><div class="col-lg-9">
<div class="card shadow-sm"><div class="card-body">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <h1 class="h4 mb-0">{{ $page->title }}</h1>
        <span class="badge {{ $page->status === 'published' ? 'status-active' : 'text-bg-secondary' }} text-capitalize">
            {{ $page->status }}
        </span>
    </div>
    <div class="text-muted small mb-3">
        Slug: <code>{{ $page->slug }}</code> &nbsp;|&nbsp;
        {{ $page->published_at ? 'Published '.$page->published_at->format('d M Y') : 'Not published' }}
    </div>
    @if ($page->content)
        <div class="prose">{!! nl2br(e($page->content)) !!}</div>
    @else
        <p class="text-muted">No content yet.</p>
    @endif
</div></div>
</div></div>
@endsection
