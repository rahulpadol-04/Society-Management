@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Tower</label>
        <select name="tower_id" class="form-select" required>
            <option value="">— Select —</option>
            @foreach ($towers as $tower)
                <option value="{{ $tower->id }}" @selected(old('tower_id', $flat->tower_id ?? '') == $tower->id)>{{ $tower->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Unit Number</label>
        <input type="text" name="number" value="{{ old('number', $flat->number ?? '') }}" class="form-control" placeholder="A-101" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Type</label>
        <input type="text" name="type" value="{{ old('type', $flat->type ?? '') }}" class="form-control" placeholder="2BHK">
    </div>
    <div class="col-md-3">
        <label class="form-label">Carpet Area (sqft)</label>
        <input type="number" step="0.01" name="carpet_area" value="{{ old('carpet_area', $flat->carpet_area ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Built-up Area (sqft)</label>
        <input type="number" step="0.01" name="built_up_area" value="{{ old('built_up_area', $flat->built_up_area ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Bedrooms</label>
        <input type="number" name="bedrooms" value="{{ old('bedrooms', $flat->bedrooms ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Bathrooms</label>
        <input type="number" name="bathrooms" value="{{ old('bathrooms', $flat->bathrooms ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Ownership</label>
        <select name="ownership" class="form-select">
            @foreach (['owner_occupied' => 'Owner Occupied', 'rented' => 'Rented', 'self' => 'Self', 'company' => 'Company'] as $v => $l)
                <option value="{{ $v }}" @selected(old('ownership', $flat->ownership ?? 'owner_occupied') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach (['occupied' => 'Occupied', 'vacant' => 'Vacant', 'on_rent' => 'On Rent', 'under_construction' => 'Under Construction'] as $v => $l)
                <option value="{{ $v }}" @selected(old('status', $flat->status ?? 'vacant') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Owner / Primary Resident</label>
        <select name="owner_id" class="form-select">
            <option value="">— None —</option>
            @foreach ($residents as $resident)
                <option value="{{ $resident->id }}" @selected(old('owner_id', $flat->owner_id ?? '') == $resident->id)>{{ $resident->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Monthly Maintenance Override (₹)</label>
        <input type="number" step="0.01" name="maintenance_amount" value="{{ old('maintenance_amount', $flat->maintenance_amount ?? '') }}" class="form-control" placeholder="Leave blank to use head charges">
    </div>
</div>
