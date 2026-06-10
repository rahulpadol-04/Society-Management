{{-- Shared form partial used by both create.blade.php and edit.blade.php --}}
<div class="row g-3">
    {{-- Name & Code --}}
    <div class="col-md-8">
        <label class="form-label">Asset Name <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $asset->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Asset Code</label>
        <input type="text" name="code" value="{{ old('code', $asset->code ?? '') }}" class="form-control" placeholder="e.g. ELV-001">
    </div>

    {{-- Category & Status --}}
    <div class="col-md-6">
        <label class="form-label">Category</label>
        <select name="asset_category_id" class="form-select">
            <option value="">— Select Category —</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('asset_category_id', $asset->asset_category_id ?? '') == $cat->id)>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach (['active', 'under_maintenance', 'retired', 'disposed'] as $st)
                <option value="{{ $st }}" @selected(old('status', $asset->status ?? 'active') === $st)>
                    {{ ucfirst(str_replace('_', ' ', $st)) }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Location & Tower --}}
    <div class="col-md-6">
        <label class="form-label">Location</label>
        <input type="text" name="location" value="{{ old('location', $asset->location ?? '') }}" class="form-control" placeholder="e.g. Basement, Terrace">
    </div>
    <div class="col-md-6">
        <label class="form-label">Tower</label>
        <select name="tower_id" class="form-select">
            <option value="">— None —</option>
            @foreach ($towers as $tower)
                <option value="{{ $tower->id }}" @selected(old('tower_id', $asset->tower_id ?? '') == $tower->id)>
                    {{ $tower->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Description --}}
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control">{{ old('description', $asset->description ?? '') }}</textarea>
    </div>

    <hr class="col-12 my-1">
    <div class="col-12"><h6 class="text-muted">Purchase & Financial Details</h6></div>

    {{-- Purchase Date & Cost --}}
    <div class="col-md-4">
        <label class="form-label">Purchase Date</label>
        <input type="date" name="purchase_date" value="{{ old('purchase_date', isset($asset) ? $asset->purchase_date?->toDateString() : '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Purchase Cost (₹)</label>
        <input type="number" name="purchase_cost" value="{{ old('purchase_cost', $asset->purchase_cost ?? 0) }}" class="form-control" step="0.01" min="0">
    </div>
    <div class="col-md-4">
        <label class="form-label">Salvage Value (₹)</label>
        <input type="number" name="salvage_value" value="{{ old('salvage_value', $asset->salvage_value ?? 0) }}" class="form-control" step="0.01" min="0">
    </div>

    {{-- Depreciation --}}
    <div class="col-md-4">
        <label class="form-label">Depreciation Method</label>
        <select name="depreciation_method" class="form-select">
            @foreach (['straight_line' => 'Straight Line', 'declining_balance' => 'Declining Balance', 'none' => 'None'] as $val => $label)
                <option value="{{ $val }}" @selected(old('depreciation_method', $asset->depreciation_method ?? 'straight_line') === $val)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Annual Depreciation Rate (%)</label>
        <input type="number" name="depreciation_rate" value="{{ old('depreciation_rate', $asset->depreciation_rate ?? '') }}" class="form-control" step="0.01" min="0" max="100" placeholder="Overrides category rate">
    </div>
    <div class="col-md-4">
        <label class="form-label">Useful Life (Years)</label>
        <input type="number" name="useful_life_years" value="{{ old('useful_life_years', $asset->useful_life_years ?? '') }}" class="form-control" min="1" placeholder="Overrides category life">
    </div>

    {{-- Warranty --}}
    <div class="col-md-6">
        <label class="form-label">Warranty Until</label>
        <input type="date" name="warranty_until" value="{{ old('warranty_until', isset($asset) ? $asset->warranty_until?->toDateString() : '') }}" class="form-control">
    </div>

    {{-- Image --}}
    <div class="col-md-6">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        @if (!empty($asset->image))
            <div class="mt-1 small text-muted">Current: {{ basename($asset->image) }}</div>
        @endif
    </div>
</div>
