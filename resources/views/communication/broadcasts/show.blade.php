@extends('layouts.app')
@section('title', 'Broadcast: '.Str::limit($broadcast->title, 40))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('communication.index') }}">Communication</a></li>
    <li class="breadcrumb-item"><a href="{{ route('communication.broadcasts.index') }}">Broadcasts</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($broadcast->title, 30) }}</li>
@endsection

@section('page-actions')
    @can('send', $broadcast)
        @if (in_array($broadcast->status, ['draft', 'failed']))
            <form method="POST"
                  action="{{ route('communication.broadcasts.send', $broadcast) }}"
                  class="d-inline"
                  data-confirm="Send this broadcast now to all resolved recipients?">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Send Now</button>
            </form>
        @endif
    @endcan
@endsection

@section('content')
<div class="row g-3">
    {{-- Broadcast detail --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h2 class="h5 mb-0">{{ $broadcast->title }}</h2>
                <span class="badge status-{{ $broadcast->status }} text-capitalize">{{ $broadcast->status }}</span>
            </div>

            <p class="mb-3">{{ $broadcast->message }}</p>

            <div class="row small text-muted g-2">
                <div class="col-sm-4">
                    <strong>Channels:</strong><br>
                    @foreach ($broadcast->channels ?? [] as $ch)
                        <span class="badge text-bg-light text-capitalize">{{ $ch }}</span>
                    @endforeach
                </div>
                <div class="col-sm-4">
                    <strong>Audience:</strong><br>
                    <span class="text-capitalize">{{ $broadcast->audience }}</span>
                </div>
                <div class="col-sm-4">
                    <strong>Recipients:</strong><br>
                    {{ number_format($broadcast->recipients_count) }}
                </div>
                <div class="col-sm-4 mt-1">
                    <strong>Created by:</strong><br>
                    {{ $broadcast->creator?->name ?? '—' }}
                </div>
                <div class="col-sm-4 mt-1">
                    <strong>Scheduled:</strong><br>
                    {{ $broadcast->scheduled_at?->format('d M Y H:i') ?? 'Immediately' }}
                </div>
                <div class="col-sm-4 mt-1">
                    <strong>Sent at:</strong><br>
                    {{ $broadcast->sent_at?->format('d M Y H:i') ?? '—' }}
                </div>
            </div>
        </div></div>

        {{-- Recipients table --}}
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Recipients</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable mb-0">
                        <thead>
                            <tr>
                                <th>Recipient</th>
                                <th>Channel</th>
                                <th>Status</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($broadcast->recipients as $rec)
                            <tr>
                                <td>{{ $rec->recipient?->name ?? '(deleted user)' }}</td>
                                <td class="text-capitalize">{{ $rec->channel }}</td>
                                <td>
                                    <span class="badge status-{{ $rec->status }} text-capitalize">
                                        {{ $rec->status }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $rec->sent_at?->format('d M Y H:i') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No recipients yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Side panel --}}
    <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-body">
            <h3 class="h6 mb-3">Summary</h3>
            <dl class="row small mb-0">
                <dt class="col-6">Total recipients</dt>
                <dd class="col-6">{{ number_format($broadcast->recipients_count) }}</dd>

                <dt class="col-6">Sent</dt>
                <dd class="col-6">{{ number_format($broadcast->recipients->where('status', 'sent')->count()) }}</dd>

                <dt class="col-6">Pending</dt>
                <dd class="col-6">{{ number_format($broadcast->recipients->where('status', 'pending')->count()) }}</dd>

                <dt class="col-6">Failed</dt>
                <dd class="col-6">{{ number_format($broadcast->recipients->where('status', 'failed')->count()) }}</dd>

                <dt class="col-6">Read</dt>
                <dd class="col-6">{{ number_format($broadcast->recipients->where('status', 'read')->count()) }}</dd>
            </dl>
        </div></div>
    </div>
</div>
@endsection
