{{-- Shared form partial for create and edit --}}
<div class="row g-3">
    {{-- Name --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $staff->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Employee Code --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Employee Code</label>
        <input type="text" name="employee_code" value="{{ old('employee_code', $staff->employee_code ?? '') }}"
               class="form-control @error('employee_code') is-invalid @enderror">
        @error('employee_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Designation --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Designation</label>
        <input type="text" name="designation" value="{{ old('designation', $staff->designation ?? '') }}"
               class="form-control @error('designation') is-invalid @enderror">
        @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Department --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
        <select name="department" class="form-select @error('department') is-invalid @enderror" required>
            <option value="">— Select Department —</option>
            @foreach (['security','housekeeping','maintenance','admin','gardening','plumbing','electrical','other'] as $dept)
                <option value="{{ $dept }}" @selected(old('department', $staff->department ?? '') === $dept)>
                    {{ ucfirst($dept) }}
                </option>
            @endforeach
        </select>
        @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Phone --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $staff->phone ?? '') }}"
               class="form-control @error('phone') is-invalid @enderror">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Email --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" value="{{ old('email', $staff->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Joining Date --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Joining Date</label>
        <input type="date" name="joining_date" value="{{ old('joining_date', isset($staff->joining_date) ? $staff->joining_date->format('Y-m-d') : '') }}"
               class="form-control @error('joining_date') is-invalid @enderror">
        @error('joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Salary --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Monthly Salary (₹)</label>
        <input type="number" name="salary" step="0.01" min="0"
               value="{{ old('salary', $staff->salary ?? '0') }}"
               class="form-control @error('salary') is-invalid @enderror">
        @error('salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Shift --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Default Shift</label>
        <select name="shift" class="form-select @error('shift') is-invalid @enderror">
            <option value="">— No default shift —</option>
            @foreach (['morning','evening','night','general'] as $s)
                <option value="{{ $s }}" @selected(old('shift', $staff->shift ?? '') === $s)>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>
        @error('shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            @foreach (['active','inactive','on_leave','terminated'] as $st)
                <option value="{{ $st }}" @selected(old('status', $staff->status ?? 'active') === $st)>
                    {{ ucwords(str_replace('_', ' ', $st)) }}
                </option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Address --}}
    <div class="col-12">
        <label class="form-label fw-semibold">Address</label>
        <textarea name="address" rows="2"
                  class="form-control @error('address') is-invalid @enderror">{{ old('address', $staff->address ?? '') }}</textarea>
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Photo --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Photo</label>
        @if (!empty($staff->photo))
            <div class="mb-2">
                <img src="{{ asset('storage/'.$staff->photo) }}" class="rounded" style="height:60px;">
            </div>
        @endif
        <input type="file" name="photo" accept="image/*"
               class="form-control @error('photo') is-invalid @enderror">
        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
