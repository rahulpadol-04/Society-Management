@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" value="{{ old('name', $tower->name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Code</label>
        <input type="text" name="code" value="{{ old('code', $tower->code ?? '') }}" class="form-control" placeholder="A">
    </div>
    <div class="col-md-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select">
            @foreach (['tower', 'block', 'building', 'wing'] as $t)
                <option value="{{ $t }}" @selected(old('type', $tower->type ?? 'tower') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Total Floors</label>
        <input type="number" name="total_floors" min="0" value="{{ old('total_floors', $tower->total_floors ?? 0) }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Units / Floor</label>
        <input type="number" name="units_per_floor" min="0" value="{{ old('units_per_floor', $tower->units_per_floor ?? 0) }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach (['active', 'inactive'] as $s)
                <option value="{{ $s }}" @selected(old('status', $tower->status ?? 'active') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="2" class="form-control">{{ old('description', $tower->description ?? '') }}</textarea>
    </div>
    @if (! isset($tower))
        <div class="col-12 form-check ms-2">
            <input class="form-check-input" type="checkbox" name="scaffold" value="1" id="scaffold" checked>
            <label class="form-check-label" for="scaffold">Auto-generate floors &amp; units from the values above</label>
        </div>
    @endif
</div>
