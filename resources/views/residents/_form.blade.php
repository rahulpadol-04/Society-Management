@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $resident->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select">
            @foreach (['owner' => 'Owner', 'tenant' => 'Tenant', 'family_member' => 'Family Member'] as $v => $l)
                <option value="{{ $v }}" @selected(old('type', $resident->type ?? 'owner') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $resident->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $resident->phone ?? '') }}"
               class="form-control @error('phone') is-invalid @enderror">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Flat / Unit</label>
        <select name="flat_id" class="form-select">
            <option value="">— None —</option>
            @foreach ($flats as $flat)
                <option value="{{ $flat->id }}" @selected(old('flat_id', $resident->flat_id ?? '') == $flat->id)>{{ $flat->number }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Link to User Account</label>
        <select name="user_id" class="form-select">
            <option value="">— None —</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $resident->user_id ?? '') == $user->id)>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Relation (for family members)</label>
        <input type="text" name="relation" value="{{ old('relation', $resident->relation ?? '') }}"
               class="form-control" placeholder="spouse, child, parent…">
    </div>

    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'moved_out' => 'Moved Out'] as $v => $l)
                <option value="{{ $v }}" @selected(old('status', $resident->status ?? 'active') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Move-in Date</label>
        <input type="date" name="move_in_date"
               value="{{ old('move_in_date', isset($resident) ? $resident->move_in_date?->format('Y-m-d') : '') }}"
               class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Move-out Date</label>
        <input type="date" name="move_out_date"
               value="{{ old('move_out_date', isset($resident) ? $resident->move_out_date?->format('Y-m-d') : '') }}"
               class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Photo</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
        @if (!empty($resident->photo))
            <div class="mt-1 small text-muted">Current: {{ basename($resident->photo) }}</div>
        @endif
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input type="checkbox" name="is_primary" value="1" class="form-check-input"
                   id="isPrimary" @checked(old('is_primary', $resident->is_primary ?? false))>
            <label class="form-check-label" for="isPrimary">Primary resident for this flat</label>
        </div>
    </div>
</div>
