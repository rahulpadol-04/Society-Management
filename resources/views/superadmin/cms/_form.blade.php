{{-- Shared form partial for CMS page create/edit --}}
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $page->title ?? '') }}" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Slug <span class="text-danger">*</span></label>
        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
               value="{{ old('slug', $page->slug ?? '') }}" required>
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">Content</label>
        <textarea name="content" class="form-control" rows="12">{{ old('content', $page->content ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Meta Title</label>
        <input type="text" name="meta_title" class="form-control"
               value="{{ old('meta_title', $page->meta_title ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Meta Description</label>
        <input type="text" name="meta_description" class="form-control"
               value="{{ old('meta_description', $page->meta_description ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="draft" {{ old('status', $page->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ old('status', $page->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
