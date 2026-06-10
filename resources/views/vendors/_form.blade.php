{{-- Shared form partial used by create and edit views --}}
<div class="mb-3">
    <label class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" name="name" value="{{ old('name', $vendor->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row g-3">
    <div class="col-md-6 mb-3">
        <label class="form-label">Company</label>
        <input type="text" name="company" value="{{ old('company', $vendor->company ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Category <span class="text-danger">*</span></label>
        <select name="category" class="form-select">
            @foreach (['plumbing', 'electrical', 'housekeeping', 'security', 'landscaping', 'elevator', 'pest_control', 'general', 'other'] as $cat)
                <option value="{{ $cat }}" @selected(old('category', $vendor->category ?? 'general') === $cat)>{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4 mb-3">
        <label class="form-label">Contact Person</label>
        <input type="text" name="contact_person" value="{{ old('contact_person', $vendor->contact_person ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $vendor->phone ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $vendor->email ?? '') }}" class="form-control">
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 mb-3">
        <label class="form-label">GSTIN</label>
        <input type="text" name="gstin" value="{{ old('gstin', $vendor->gstin ?? '') }}" class="form-control" maxlength="20">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach (['active', 'inactive', 'blacklisted'] as $st)
                <option value="{{ $st }}" @selected(old('status', $vendor->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" rows="2" class="form-control">{{ old('address', $vendor->address ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Notes</label>
    <textarea name="notes" rows="3" class="form-control">{{ old('notes', $vendor->notes ?? '') }}</textarea>
</div>
