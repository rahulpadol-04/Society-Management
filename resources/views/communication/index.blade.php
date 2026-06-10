@extends('layouts.app')
@section('title', 'Communication')

@section('page-actions')
    @can('create', App\Models\Broadcast::class)
        <a href="{{ route('communication.broadcasts.create') }}" class="btn btn-primary">
            <i class="bi bi-send"></i> Compose Broadcast
        </a>
    @endcan
@endsection

@section('content')
{{-- KPI cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-soft-primary"><i class="bi bi-broadcast"></i></span>
                <div class="min-w-0">
                    <div class="stat-value text-truncate">{{ number_format($kpi['broadcasts_sent']) }}</div>
                    <div class="stat-label">Broadcasts Sent</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-soft-info"><i class="bi bi-file-earmark-text"></i></span>
                <div class="min-w-0">
                    <div class="stat-value text-truncate">{{ number_format($kpi['templates']) }}</div>
                    <div class="stat-label">Message Templates</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-soft-success"><i class="bi bi-people"></i></span>
                <div class="min-w-0">
                    <div class="stat-value text-truncate">{{ number_format($kpi['recipients_reached']) }}</div>
                    <div class="stat-label">Recipients Reached</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick-nav links --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="{{ route('communication.broadcasts.create') }}" class="card shadow-sm text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-send fs-2 text-primary d-block mb-2"></i>
                <div class="fw-semibold">Compose Broadcast</div>
                <div class="text-muted small">Email, SMS, Push, WhatsApp</div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('communication.templates.index') }}" class="card shadow-sm text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-file-earmark-text fs-2 text-info d-block mb-2"></i>
                <div class="fw-semibold">Message Templates</div>
                <div class="text-muted small">Reusable templates per channel</div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('communication.messages.index') }}" class="card shadow-sm text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-chat-dots fs-2 text-success d-block mb-2"></i>
                <div class="fw-semibold">Messages / Inbox</div>
                <div class="text-muted small">Direct &amp; group conversations</div>
            </div>
        </a>
    </div>
</div>

{{-- Recent Broadcasts table --}}
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Recent Broadcasts</span>
        <a href="{{ route('communication.broadcasts.index') }}" class="btn btn-sm btn-outline-secondary">View all</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Channels</th>
                        <th>Audience</th>
                        <th>Status</th>
                        <th>Recipients</th>
                        <th>Sent At</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($recentBroadcasts as $broadcast)
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
                    <tr><td colspan="7" class="text-center text-muted py-4">No broadcasts yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
