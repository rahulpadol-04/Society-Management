@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select">
            @foreach (['car' => 'Car', 'bike' => 'Bike', 'other' => 'Other'] as $v => $l)
                <option value="{{ $v }}" @selected(old('type', $vehicle->type ?? 'car') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-8">
        <label class="form-label">Registration Number <span class="text-danger">*</span></label>
        <input type="text" name="registration_number"
               value="{{ old('registration_number', $vehicle->registration_number ?? '') }}"
               class="form-control @error('registration_number') is-invalid @enderror"
               placeholder="MH-01-AB-1234" required>
        @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Make</label>
        <input type="text" name="make" value="{{ old('make', $vehicle->make ?? '') }}"
               class="form-control" placeholder="Maruti, Honda…">
    </div>

    <div class="col-md-4">
        <label class="form-label">Model</label>
        <input type="text" name="model" value="{{ old('model', $vehicle->model ?? '') }}"
               class="form-control" placeholder="Swift, Activa…">
    </div>

    <div class="col-md-4">
        <label class="form-label">Color</label>
        <input type="text" name="color" value="{{ old('color', $vehicle->color ?? '') }}"
               class="form-control" placeholder="White, Silver…">
    </div>

    <div class="col-md-6">
        <label class="form-label">Resident</label>
        <select name="resident_id" class="form-select">
            <option value="">— None —</option>
            @foreach ($residents as $resident)
                <option value="{{ $resident->id }}"
                        @selected(old('resident_id', $vehicle->resident_id ?? request('resident_id')) == $resident->id)>
                    {{ $resident->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Flat / Unit</label>
        <select name="flat_id" class="form-select">
            <option value="">— None —</option>
            @foreach ($flats as $flat)
                <option value="{{ $flat->id }}" @selected(old('flat_id', $vehicle->flat_id ?? '') == $flat->id)>{{ $flat->number }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">RFID Tag</label>
        <input type="text" name="rfid_tag" value="{{ old('rfid_tag', $vehicle->rfid_tag ?? '') }}"
               class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="active" @selected(old('status', $vehicle->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $vehicle->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
    </div>
</div>
