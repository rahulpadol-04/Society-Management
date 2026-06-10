@extends('layouts.app')
@section('title', 'Asset Categories')

@section('page-actions')
    @can('create', App\Models\AssetCategory::class)
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="bi bi-plus-lg"></i> Add Category
        </button>
    @endcan
    <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary ms-2"><i class="bi bi-arrow-left"></i> Assets</a>
@endsection

@section('content')
<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Name</th><th>Depreciation Rate</th><th>Useful Life</th><th>Assets</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td class="fw-semibold">{{ $category->name }}</td>
                    <td>{{ $category->depreciation_rate }}% / yr</td>
                    <td>{{ $category->useful_life_years ? $category->useful_life_years.' yrs' : '—' }}</td>
                    <td>{{ $category->assets_count }}</td>
                    <td>
                        <span class="badge text-bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        @can('update', $category)
                            <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editCategoryModal"
                                data-id="{{ $category->id }}"
                                data-name="{{ $category->name }}"
                                data-rate="{{ $category->depreciation_rate }}"
                                data-life="{{ $category->useful_life_years }}"
                                data-active="{{ $category->is_active ? '1' : '0' }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                        @endcan
                        @can('delete', $category)
                            @if ($category->assets_count === 0)
                                <form method="POST" action="{{ route('assets.categories.destroy', $category) }}" class="d-inline" data-confirm="Delete category {{ $category->name }}?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No categories yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>

{{-- Create Modal --}}
@can('create', App\Models\AssetCategory::class)
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('assets.categories.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Asset Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Annual Depreciation Rate (%)</label>
                            <input type="number" name="depreciation_rate" class="form-control" step="0.01" min="0" max="100" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Useful Life (Years)</label>
                            <input type="number" name="useful_life_years" class="form-control" min="1">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" id="createIsActive" value="1" checked>
                                <label class="form-check-label" for="createIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- Edit Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editCategoryForm" action="">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Annual Depreciation Rate (%)</label>
                            <input type="number" name="depreciation_rate" id="editRate" class="form-control" step="0.01" min="0" max="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Useful Life (Years)</label>
                            <input type="number" name="useful_life_years" id="editLife" class="form-control" min="1">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                                <label class="form-check-label" for="editIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function (event) {
    var btn  = event.relatedTarget;
    var id   = btn.getAttribute('data-id');
    var form = document.getElementById('editCategoryForm');

    form.action = '/assets/categories/' + id;
    document.getElementById('editName').value  = btn.getAttribute('data-name');
    document.getElementById('editRate').value  = btn.getAttribute('data-rate');
    document.getElementById('editLife').value  = btn.getAttribute('data-life');
    document.getElementById('editIsActive').checked = btn.getAttribute('data-active') === '1';
});
</script>
@endpush
@endsection
