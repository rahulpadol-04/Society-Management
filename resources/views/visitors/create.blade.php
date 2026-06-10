@extends('layouts.app')
@section('title', 'Request a Visitor Pass')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('visitors.index') }}">Visitors</a></li>
    <li class="breadcrumb-item active">New Pass</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-8">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('visitors.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Visitor Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                        @foreach (['guest', 'delivery', 'cab', 'service', 'vendor'] as $t)
                            <option value="{{ $t }}" @selected(old('type', 'guest') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Flat</label>
                    <select name="flat_id" class="form-select @error('flat_id') is-invalid @enderror">
                        <option value="">— Select flat —</option>
                        @foreach ($flats as $flat)
                            <option value="{{ $flat->id }}" @selected(old('flat_id') == $flat->id)>
                                {{ $flat->label ?? $flat->number }}
                            </option>
                        @endforeach
                    </select>
                    @error('flat_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="purpose" value="{{ old('purpose') }}" class="form-control" placeholder="e.g. Weekend visit, Parcel delivery…">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expected At</label>
                    <input type="datetime-local" name="expected_at" value="{{ old('expected_at') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valid Until</label>
                    <input type="datetime-local" name="valid_until" value="{{ old('valid_until') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Vehicle Number</label>
                    <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" class="form-control" placeholder="e.g. MH01AB1234">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Max Entries</label>
                    <input type="number" name="max_entries" value="{{ old('max_entries', 1) }}" class="form-control" min="1" max="100">
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-qr-code"></i> Generate Pass</button>
                <a href="{{ route('visitors.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
