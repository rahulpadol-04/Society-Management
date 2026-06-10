@extends('layouts.app')
@section('title', 'Society Documents')

@section('page-actions')
    @can('create', App\Models\SocietyDocument::class)
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDoc"><i class="bi bi-upload"></i> Upload</button>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead><tr><th>Title</th><th>Category</th><th>Size</th><th>Visibility</th><th>Uploaded</th><th></th></tr></thead>
            <tbody>
            @forelse ($documents as $doc)
                <tr>
                    <td class="fw-semibold"><i class="bi bi-file-earmark me-1"></i>{{ $doc->title }}</td>
                    <td><span class="badge text-bg-light text-capitalize">{{ $doc->category }}</span></td>
                    <td>{{ $doc->human_size }}</td>
                    <td>{!! $doc->is_public ? '<span class="badge text-bg-success">Residents</span>' : '<span class="badge text-bg-secondary">Admins</span>' !!}</td>
                    <td class="text-muted small">{{ $doc->created_at->format('d M Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                        @can('delete', $doc)
                        <form method="POST" action="{{ route('documents.destroy', $doc) }}" class="d-inline" data-confirm="Delete this document?">
                            @csrf @method('DELETE') <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No documents uploaded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>

@can('create', App\Models\SocietyDocument::class)
<div class="modal fade" id="uploadDoc" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Upload Document</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Title</label><input name="title" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Category</label>
                <select name="category" class="form-select">
                    @foreach (['legal', 'financial', 'noc', 'circular', 'agreement', 'other'] as $c)<option value="{{ $c }}">{{ ucfirst($c) }}</option>@endforeach
                </select>
            </div>
            <div class="mb-2"><label class="form-label">File</label><input type="file" name="file" class="form-control" required></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="is_public" value="1" id="pub"><label class="form-check-label" for="pub">Visible to residents</label></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Upload</button></div>
    </form>
</div></div></div>
@endcan
@endsection
