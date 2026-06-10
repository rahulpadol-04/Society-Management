@extends('layouts.app')
@section('title', 'Unit '.$flat->number)

@section('page-actions')
    @can('update', $flat)
        <a href="{{ route('flats.edit', $flat) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    @endcan
    @can('delete', $flat)
        <form method="POST" action="{{ route('flats.destroy', $flat) }}" class="d-inline" data-confirm="Delete this unit?">
            @csrf @method('DELETE') <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h5">{{ $flat->number }}</h2>
            <dl class="row small mb-0">
                <dt class="col-5">Tower</dt><dd class="col-7">{{ $flat->tower?->name ?? '—' }}</dd>
                <dt class="col-5">Floor</dt><dd class="col-7">{{ $flat->floor?->name ?? '—' }}</dd>
                <dt class="col-5">Type</dt><dd class="col-7">{{ $flat->type ?? '—' }}</dd>
                <dt class="col-5">Carpet Area</dt><dd class="col-7">{{ $flat->carpet_area ? $flat->carpet_area.' sqft' : '—' }}</dd>
                <dt class="col-5">Built-up Area</dt><dd class="col-7">{{ $flat->built_up_area ? $flat->built_up_area.' sqft' : '—' }}</dd>
                <dt class="col-5">Bedrooms</dt><dd class="col-7">{{ $flat->bedrooms ?? '—' }}</dd>
                <dt class="col-5">Ownership</dt><dd class="col-7 text-capitalize">{{ str_replace('_', ' ', $flat->ownership) }}</dd>
                <dt class="col-5">Status</dt><dd class="col-7 text-capitalize">{{ str_replace('_', ' ', $flat->status) }}</dd>
                <dt class="col-5">Owner</dt><dd class="col-7">{{ $flat->owner?->name ?? '—' }}</dd>
                <dt class="col-5">Maintenance</dt><dd class="col-7">{{ $flat->maintenance_amount ? money($flat->maintenance_amount) : 'Head charges' }}</dd>
            </dl>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6">Parking Slots</h2>
            @forelse ($flat->parkingSlots as $slot)
                <span class="badge text-bg-light me-1">{{ $slot->code }} ({{ ucfirst($slot->type) }})</span>
            @empty
                <p class="text-muted small mb-0">No parking allocated.</p>
            @endforelse
        </div></div>
    </div>
</div>
@endsection
