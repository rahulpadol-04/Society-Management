@extends('layouts.app')
@section('title', $tower->name)

@section('page-actions')
    @can('update', $tower)
        <form method="POST" action="{{ route('towers.scaffold', $tower) }}" class="d-inline" data-confirm="Generate floors & units for this tower?">
            @csrf <button class="btn btn-outline-secondary"><i class="bi bi-magic"></i> Generate Units</button>
        </form>
        <a href="{{ route('towers.edit', $tower) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    @endcan
    @can('delete', $tower)
        <form method="POST" action="{{ route('towers.destroy', $tower) }}" class="d-inline" data-confirm="Delete this tower and all its units?">
            @csrf @method('DELETE') <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6">Tower Details</h2>
            <dl class="row small mb-0">
                <dt class="col-5">Code</dt><dd class="col-7">{{ $tower->code ?? '—' }}</dd>
                <dt class="col-5">Type</dt><dd class="col-7 text-capitalize">{{ $tower->type }}</dd>
                <dt class="col-5">Floors</dt><dd class="col-7">{{ $tower->total_floors }}</dd>
                <dt class="col-5">Units</dt><dd class="col-7">{{ $tower->flats->count() }}</dd>
                <dt class="col-5">Status</dt><dd class="col-7 text-capitalize">{{ $tower->status }}</dd>
            </dl>
            @if ($tower->description)<p class="text-muted small mt-2">{{ $tower->description }}</p>@endif
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6">Units</h2>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead><tr><th>Unit</th><th>Floor</th><th>Type</th><th>Owner</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse ($tower->flats->sortBy('number') as $flat)
                        <tr>
                            <td><a href="{{ route('flats.show', $flat) }}">{{ $flat->number }}</a></td>
                            <td>{{ $flat->floor?->name ?? '—' }}</td>
                            <td>{{ $flat->type ?? '—' }}</td>
                            <td>{{ $flat->owner?->name ?? '—' }}</td>
                            <td><span class="badge text-bg-{{ in_array($flat->status, ['occupied','on_rent']) ? 'success' : 'secondary' }} text-capitalize">{{ str_replace('_', ' ', $flat->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No units yet — use "Generate Units".</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
@endsection
