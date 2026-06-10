@extends('layouts.app')
@section('title', 'Resident Directory')

@section('page-actions')
    @if (auth()->user()->can('directory.export'))
        <a href="{{ route('directory.export') }}" class="btn btn-outline-secondary"><i class="bi bi-download"></i> Export CSV</a>
    @endif
@endsection

@section('content')
<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Name</th><th>Type</th><th>Flat</th><th>Phone</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($residents as $resident)
                <tr>
                    <td class="fw-semibold">{{ $resident->name }}</td>
                    <td><span class="badge text-bg-light text-capitalize">{{ str_replace('_', ' ', $resident->type) }}</span></td>
                    <td>{{ $resident->flat?->number ?? '—' }}</td>
                    <td>{{ $resident->phone ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No residents in directory.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
