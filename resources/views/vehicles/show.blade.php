@extends('layouts.app')
@section('title', $vehicle->registration_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
    <li class="breadcrumb-item active">{{ $vehicle->registration_number }}</li>
@endsection

@section('page-actions')
    @can('update', $vehicle)
        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    @endcan
    @can('delete', $vehicle)
        <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" class="d-inline"
              data-confirm="Delete vehicle {{ $vehicle->registration_number }}?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
@endsection

@section('content')
<div class="row"><div class="col-lg-6">
    <div class="card shadow-sm"><div class="card-body">
        <h2 class="h5 mb-3">{{ $vehicle->registration_number }}</h2>
        <dl class="row small mb-0">
            <dt class="col-5">Type</dt><dd class="col-7 text-capitalize">{{ $vehicle->type }}</dd>
            <dt class="col-5">Make</dt><dd class="col-7">{{ $vehicle->make ?? '—' }}</dd>
            <dt class="col-5">Model</dt><dd class="col-7">{{ $vehicle->model ?? '—' }}</dd>
            <dt class="col-5">Color</dt><dd class="col-7">{{ $vehicle->color ?? '—' }}</dd>
            <dt class="col-5">RFID Tag</dt><dd class="col-7">{{ $vehicle->rfid_tag ?? '—' }}</dd>
            <dt class="col-5">Resident</dt>
            <dd class="col-7">
                @if ($vehicle->resident)
                    <a href="{{ route('residents.show', $vehicle->resident) }}">{{ $vehicle->resident->name }}</a>
                @else
                    —
                @endif
            </dd>
            <dt class="col-5">Flat</dt><dd class="col-7">{{ $vehicle->flat?->number ?? '—' }}</dd>
            <dt class="col-5">Parking Slot</dt><dd class="col-7">{{ $vehicle->parkingSlot?->code ?? '—' }}</dd>
            <dt class="col-5">Status</dt>
            <dd class="col-7">
                <span class="badge text-bg-{{ $vehicle->status === 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($vehicle->status) }}
                </span>
            </dd>
        </dl>
    </div></div>
</div></div>
@endsection
