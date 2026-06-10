<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name"
               value="{{ old('name', $head->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror"
               required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Code</label>
        <input type="text" name="code"
               value="{{ old('code', $head->code ?? '') }}"
               class="form-control @error('code') is-invalid @enderror"
               placeholder="e.g. MC">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach (['fixed' => 'Fixed', 'per_sqft' => 'Per Sq.Ft.', 'per_unit' => 'Per Unit', 'percentage' => 'Percentage'] as $val => $label)
                <option value="{{ $val }}" @selected(old('type', $head->type ?? 'fixed') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Amount <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" step="0.01" min="0" name="amount"
                   value="{{ old('amount', $head->amount ?? '') }}"
                   class="form-control @error('amount') is-invalid @enderror"
                   required>
        </div>
        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Frequency <span class="text-danger">*</span></label>
        <select name="frequency" class="form-select @error('frequency') is-invalid @enderror" required>
            @foreach (['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'half_yearly' => 'Half-Yearly', 'yearly' => 'Yearly', 'one_time' => 'One Time'] as $val => $label)
                <option value="{{ $val }}" @selected(old('frequency', $head->frequency ?? 'monthly') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_taxable" value="1"
                   id="is_taxable" @checked(old('is_taxable', $head->is_taxable ?? false))>
            <label class="form-check-label" for="is_taxable">Apply GST on this head</label>
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label">GST %</label>
        <input type="number" step="0.01" min="0" max="100" name="gst_percentage"
               value="{{ old('gst_percentage', $head->gst_percentage ?? 18) }}"
               class="form-control @error('gst_percentage') is-invalid @enderror"
               placeholder="{{ config('communityos.billing.gst_percentage') }}">
        @error('gst_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                   id="is_active" @checked(old('is_active', $head->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="2"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $head->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
