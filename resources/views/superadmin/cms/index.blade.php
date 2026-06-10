@extends('layouts.app')
@section('title', 'CMS Pages')

@section('page-actions')
    @can('create', App\Models\CmsPage::class)
        <a href="{{ route('cms.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Page
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
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($pages as $page)
                    <tr>
                        <td class="fw-semibold">{{ $page->title }}</td>
                        <td><code class="small">{{ $page->slug }}</code></td>
                        <td>
                            <span class="badge {{ $page->status === 'published' ? 'status-active' : 'text-bg-secondary' }} text-capitalize">
                                {{ $page->status }}
                            </span>
                        </td>
                        <td>{{ $page->published_at?->format('d M Y') ?? '—' }}</td>
                        <td>{{ $page->updated_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                @can('update', $page)
                                    <a href="{{ route('cms.edit', $page) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('publish', $page)
                                    <form method="POST" action="{{ route('cms.publish', $page) }}">
                                        @csrf
                                        <button class="btn btn-sm {{ $page->status === 'published' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $page->status === 'published' ? 'Unpublish' : 'Publish' }}">
                                            <i class="bi {{ $page->status === 'published' ? 'bi-eye-slash' : 'bi-globe' }}"></i>
                                        </button>
                                    </form>
                                @endcan
                                @can('delete', $page)
                                    <form method="POST" action="{{ route('cms.destroy', $page) }}"
                                          data-confirm="Delete page &quot;{{ $page->title }}&quot;?">
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
                    <tr><td colspan="6" class="text-center text-muted py-4">No CMS pages yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
