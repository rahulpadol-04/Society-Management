{{-- Shared form partial for create/edit plans --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Plan Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $plan->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug <span class="text-danger">*</span></label>
        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
               value="{{ old('slug', $plan->slug ?? '') }}" required>
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Billing Cycle <span class="text-danger">*</span></label>
        <select name="billing_cycle" class="form-select @error('billing_cycle') is-invalid @enderror" required>
            @foreach (['trial', 'monthly', 'quarterly', 'annual'] as $cycle)
                <option value="{{ $cycle }}" {{ old('billing_cycle', $plan->billing_cycle ?? '') === $cycle ? 'selected' : '' }}>
                    {{ ucfirst($cycle) }}
                </option>
            @endforeach
        </select>
        @error('billing_cycle')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Price <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" name="price" step="0.01" min="0"
                   class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price', $plan->price ?? '0') }}" required>
        </div>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Trial Days</label>
        <input type="number" name="trial_days" min="0"
               class="form-control @error('trial_days') is-invalid @enderror"
               value="{{ old('trial_days', $plan->trial_days ?? '0') }}">
        @error('trial_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Max Units</label>
        <input type="number" name="max_units" min="1" placeholder="Unlimited"
               class="form-control"
               value="{{ old('max_units', $plan->max_units ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Max Users</label>
        <input type="number" name="max_users" min="1" placeholder="Unlimited"
               class="form-control"
               value="{{ old('max_users', $plan->max_users ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Max Storage (MB)</label>
        <input type="number" name="max_storage_mb" min="1" placeholder="Unlimited"
               class="form-control"
               value="{{ old('max_storage_mb', $plan->max_storage_mb ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $plan->description ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label d-block">Features</label>
        <div class="row g-2">
            @foreach ($features as $feat)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="features[]"
                               value="{{ $feat }}" id="feat_{{ $feat }}"
                               {{ in_array($feat, old('features', $plan->features ?? [])) ? 'checked' : '' }}>
                        <label class="form-check-label text-capitalize" for="feat_{{ $feat }}">{{ $feat }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" min="0" class="form-control"
               value="{{ old('sort_order', $plan->sort_order ?? '0') }}">
    </div>
    <div class="col-md-4 d-flex align-items-end gap-4 pb-1">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                   {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured"
                   {{ old('is_featured', $plan->is_featured ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_featured">Featured</label>
        </div>
    </div>
</div>
