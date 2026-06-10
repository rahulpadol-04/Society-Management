@extends('layouts.app')
@section('title', 'Broadcasts')

@section('page-actions')
    @can('create', App\Models\Broadcast::class)
        <a href="{{ route('communication.broadcasts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Broadcast
        </a>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Channels</th>
                        <th>Audience</th>
                        <th>Status</th>
                        <th>Recipients</th>
                        <th>Created By</th>
                        <th>Sent At</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($broadcasts as $broadcast)
                    <tr>
                        <td>
                            <a href="{{ route('communication.broadcasts.show', $broadcast) }}" class="fw-semibold">
                                {{ \Illuminate\Support\Str::limit($broadcast->title, 50) }}
                            </a>
                        </td>
                        <td>
                            @foreach ($broadcast->channels ?? [] as $ch)
                                <span class="badge text-bg-light text-capitalize">{{ $ch }}</span>
                            @endforeach
                        </td>
                        <td class="text-capitalize">{{ $broadcast->audience }}</td>
                        <td>
                            <span class="badge status-{{ $broadcast->status }} text-capitalize">
                                {{ $broadcast->status }}
                            </span>
                        </td>
                        <td>{{ number_format($broadcast->recipients_count) }}</td>
                        <td>{{ $broadcast->creator?->name ?? '—' }}</td>
                        <td class="text-muted small">
                            {{ $broadcast->sent_at ? $broadcast->sent_at->format('d M Y H:i') : '—' }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('communication.broadcasts.show', $broadcast) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No broadcasts yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $broadcasts->links() }}
    </div>
</div>
@endsection
