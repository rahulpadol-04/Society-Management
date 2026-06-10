@extends('layouts.app')
@section('title', 'Generate Maintenance Bills')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('maintenance.index') }}">Maintenance Billing</a></li>
    <li class="breadcrumb-item active">Generate Bills</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-7">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('maintenance.generate.post') }}">
            @csrf

            <div class="mb-4">
                <label class="form-label fw-semibold">Billing Period <span class="text-danger">*</span></label>
                <input type="month"
                       name="period"
                       value="{{ old('period', $defaultPeriod) }}"
                       class="form-control @error('period') is-invalid @enderror"
                       required>
                @error('period')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Bills will be generated for all active flats for this month.</div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Tower Filter <span class="text-muted fw-normal">(optional — leave blank for all towers)</span></label>
                @foreach ($towers as $tower)
                    <div class="card mb-2">
                        <div class="card-body py-2">
                            <div class="fw-semibold small">{{ $tower->name }}</div>
                            <div class="row g-1 mt-1">
                                @foreach ($tower->flats as $flat)
                                    <div class="col-auto">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox"
                                                   name="flat_ids[]"
                                                   value="{{ $flat->id }}"
                                                   id="flat_{{ $flat->id }}">
                                            <label class="form-check-label small" for="flat_{{ $flat->id }}">
                                                {{ $flat->number }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="form-text">If no flats are selected, bills are generated for ALL active flats.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-lightning"></i> Generate Bills
                </button>
                <a href="{{ route('maintenance.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
