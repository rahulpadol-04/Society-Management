@extends('layouts.app')
@section('title', 'Blog')

@section('page-actions')
    @can('create', App\Models\Blog::class)
        <a href="{{ route('blog.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Post
        </a>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Published</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $post->title }}</div>
                            <div class="small text-muted"><code>{{ $post->slug }}</code></div>
                        </td>
                        <td>{{ $post->author?->name ?? '—' }}</td>
                        <td>{{ $post->category ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $post->status === 'published' ? 'status-active' : 'text-bg-secondary' }} text-capitalize">
                                {{ $post->status }}
                            </span>
                        </td>
                        <td>{{ number_format($post->views) }}</td>
                        <td>{{ $post->published_at?->format('d M Y') ?? '—' }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                @can('update', $post)
                                    <a href="{{ route('blog.edit', $post) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('publish', $post)
                                    <form method="POST" action="{{ route('blog.publish', $post) }}">
                                        @csrf
                                        <button class="btn btn-sm {{ $post->status === 'published' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $post->status === 'published' ? 'Unpublish' : 'Publish' }}">
                                            <i class="bi {{ $post->status === 'published' ? 'bi-eye-slash' : 'bi-globe' }}"></i>
                                        </button>
                                    </form>
                                @endcan
                                @can('delete', $post)
                                    <form method="POST" action="{{ route('blog.destroy', $post) }}"
                                          data-confirm="Delete post &quot;{{ $post->title }}&quot;?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No blog posts yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
