@extends('layouts.app')
@section('title', 'Journal Entries')

@section('page-actions')
    @can('create', App\Models\JournalEntry::class)
        <a href="{{ route('accounting.journals.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Entry
        </a>
    @endcan
@endsection

@section('content')
<div class="row g-2 mb-3">
    @foreach (['draft' => 'secondary', 'posted' => 'success'] as $status => $color)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body py-2">
                <div class="text-muted small text-capitalize">{{ $status }}</div>
                <div class="h5 mb-0 text-{{ $color }}">{{ $statusCounts[$status] ?? 0 }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Narration</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td>
                        <a href="{{ route('accounting.journals.show', $entry) }}" class="fw-semibold">
                            {{ $entry->reference }}
                        </a>
                    </td>
                    <td class="text-muted small">{{ $entry->entry_date->format('d M Y') }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($entry->narration ?? '—', 40) }}</td>
                    <td><span class="badge text-bg-light text-capitalize">{{ $entry->type }}</span></td>
                    <td class="fw-semibold">{{ money($entry->amount) }}</td>
                    <td>
                        <span class="badge {{ $entry->status === 'posted' ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ ucfirst($entry->status) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $entry->creator?->name ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('accounting.journals.show', $entry) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No journal entries yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
