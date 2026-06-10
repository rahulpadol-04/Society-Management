{{-- Shared form partial for blog post create/edit --}}
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $blog->title ?? '') }}" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Slug <span class="text-danger">*</span></label>
        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
               value="{{ old('slug', $blog->slug ?? '') }}" required>
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label">Excerpt</label>
        <textarea name="excerpt" class="form-control" rows="2">{{ old('excerpt', $blog->excerpt ?? '') }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control"
               value="{{ old('category', $blog->category ?? '') }}">
        <label class="form-label mt-3">Cover Image URL</label>
        <input type="text" name="cover_image" class="form-control"
               value="{{ old('cover_image', $blog->cover_image ?? '') }}" placeholder="https://...">
    </div>
    <div class="col-12">
        <label class="form-label">Content</label>
        <textarea name="content" class="form-control" rows="14">{{ old('content', $blog->content ?? '') }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="draft" {{ old('status', $blog->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ old('status', $blog->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
