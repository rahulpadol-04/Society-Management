@php $isEdit = isset($account); @endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Code <span class="text-muted small">(optional)</span></label>
        <input type="text" name="code" value="{{ old('code', $account->code ?? '') }}"
               class="form-control @error('code') is-invalid @enderror" maxlength="20">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label">Account Name <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $account->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required maxlength="180">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach (['asset', 'liability', 'equity', 'income', 'expense'] as $t)
                <option value="{{ $t }}" @selected(old('type', $account->type ?? 'asset') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Sub-type <span class="text-muted small">(optional)</span></label>
        <input type="text" name="subtype" value="{{ old('subtype', $account->subtype ?? '') }}"
               class="form-control @error('subtype') is-invalid @enderror" maxlength="60"
               placeholder="e.g. bank, cash, receivable">
        @error('subtype')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Opening Balance</label>
        <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" name="opening_balance" step="0.01" min="0"
                   value="{{ old('opening_balance', $account->opening_balance ?? 0) }}"
                   class="form-control @error('opening_balance') is-invalid @enderror">
        </div>
        @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                   @checked(old('is_active', $account->is_active ?? true))>
            <label class="form-check-label" for="isActive">Active</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="2" class="form-control @error('description') is-invalid @enderror">{{ old('description', $account->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
