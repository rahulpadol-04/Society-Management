@php
    $model = $account ?? $bank ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Account Name <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $model->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required maxlength="180">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Account Type <span class="text-danger">*</span></label>
        <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
            <option value="bank" @selected(old('account_type', $model->account_type ?? 'bank') === 'bank')>Bank</option>
            <option value="cash" @selected(old('account_type', $model->account_type ?? '') === 'cash')>Cash</option>
        </select>
        @error('account_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Bank Name</label>
        <input type="text" name="bank_name" value="{{ old('bank_name', $model->bank_name ?? '') }}"
               class="form-control @error('bank_name') is-invalid @enderror" maxlength="100">
        @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Account Number</label>
        <input type="text" name="account_number" value="{{ old('account_number', $model->account_number ?? '') }}"
               class="form-control @error('account_number') is-invalid @enderror" maxlength="30">
        @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">IFSC Code</label>
        <input type="text" name="ifsc" value="{{ old('ifsc', $model->ifsc ?? '') }}"
               class="form-control @error('ifsc') is-invalid @enderror" maxlength="15">
        @error('ifsc')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Opening Balance</label>
        <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" name="opening_balance" step="0.01" min="0"
                   value="{{ old('opening_balance', $model->opening_balance ?? 0) }}"
                   class="form-control @error('opening_balance') is-invalid @enderror">
        </div>
        @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Ledger Account Link</label>
        <select name="ledger_account_id" class="form-select @error('ledger_account_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach ($ledgerAccounts as $la)
                <option value="{{ $la->id }}" @selected(old('ledger_account_id', $model->ledger_account_id ?? null) == $la->id)>
                    {{ $la->name }}
                </option>
            @endforeach
        </select>
        @error('ledger_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="bankIsActive"
                   @checked(old('is_active', $model->is_active ?? true))>
            <label class="form-check-label" for="bankIsActive">Active</label>
        </div>
    </div>
</div>
