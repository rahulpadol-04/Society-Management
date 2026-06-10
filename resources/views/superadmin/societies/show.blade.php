@extends('layouts.app')
@section('title', $society->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('societies.index') }}">Societies</a></li>
    <li class="breadcrumb-item active">{{ $society->name }}</li>
@endsection

@section('page-actions')
    <div class="d-flex gap-2">
        @can('impersonate', $society)
            <form method="POST" action="{{ route('societies.impersonate', $society) }}">
                @csrf
                <button class="btn btn-outline-info">
                    <i class="bi bi-person-fill-gear"></i> Impersonate
                </button>
            </form>
        @endcan
        @can('suspend', $society)
            <form method="POST" action="{{ route('societies.suspend', $society) }}"
                  data-confirm="{{ $society->status === 'suspended' ? 'Reactivate this society?' : 'Suspend this society?' }}">
                @csrf
                <button class="btn {{ $society->status === 'suspended' ? 'btn-outline-success' : 'btn-outline-warning' }}">
                    <i class="bi {{ $society->status === 'suspended' ? 'bi-play-circle' : 'bi-pause-circle' }}"></i>
                    {{ $society->status === 'suspended' ? 'Reactivate' : 'Suspend' }}
                </button>
            </form>
        @endcan
        @can('update', $society)
            <a href="{{ route('societies.edit', $society) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['Plan',      $society->plan?->name ?? 'No Plan',            'bi-stack',      'info'],
            ['Status',    ucfirst($society->status),                      'bi-circle',     $society->status === 'active' ? 'success' : 'danger'],
            ['Sub. Status', ucfirst($society->subscription_status),       'bi-receipt',    'primary'],
            ['Users',     $usage['users'],                                'bi-people',     'dark'],
            ['Flats',     $usage['flats'],                                'bi-house',      'secondary'],
            ['Residents', $usage['residents'],                            'bi-person',     'warning'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $icon, $color])
        <div class="col-6 col-md-2">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon bg-soft-{{ $color }}"><i class="bi {{ $icon }}"></i></span>
                <div class="min-w-0">
                    <div class="stat-value text-truncate">{{ $value }}</div>
                    <div class="stat-label">{{ $label }}</div>
                </div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Society Details</h2>
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Name</dt>
                <dd class="col-7">{{ $society->name }}</dd>
                <dt class="col-5 text-muted">Slug</dt>
                <dd class="col-7"><code>{{ $society->slug }}</code></dd>
                <dt class="col-5 text-muted">Email</dt>
                <dd class="col-7">{{ $society->email ?? '—' }}</dd>
                <dt class="col-5 text-muted">Phone</dt>
                <dd class="col-7">{{ $society->phone ?? '—' }}</dd>
                <dt class="col-5 text-muted">City / State</dt>
                <dd class="col-7">{{ collect([$society->city, $society->state])->filter()->implode(', ') ?: '—' }}</dd>
                <dt class="col-5 text-muted">Country</dt>
                <dd class="col-7">{{ $society->country ?? '—' }}</dd>
                <dt class="col-5 text-muted">Trial Ends</dt>
                <dd class="col-7">{{ $society->trial_ends_at?->format('d M Y') ?? '—' }}</dd>
                <dt class="col-5 text-muted">Sub. Ends</dt>
                <dd class="col-7">{{ $society->subscription_ends_at?->format('d M Y') ?? '—' }}</dd>
                <dt class="col-5 text-muted">Registered</dt>
                <dd class="col-7">{{ $society->created_at->format('d M Y') }}</dd>
            </dl>
        </div></div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Subscription History</h2>
            @forelse ($society->subscriptions as $sub)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2 small">
                    <div>
                        <span class="fw-semibold">{{ $sub->plan?->name ?? '—' }}</span>
                        <span class="text-muted ms-1">{{ $sub->billing_cycle }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge status-{{ $sub->status }} text-capitalize">{{ $sub->status }}</span>
                        <span class="text-muted">{{ $sub->starts_at?->format('d M Y') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0">No subscriptions found.</p>
            @endforelse
        </div></div>
    </div>

    @if ($recentActivity->count())
    <div class="col-12">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Recent Users</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle table-sm">
                    <thead><tr><th>Name</th><th>Email</th><th>Joined</th></tr></thead>
                    <tbody>
                    @foreach ($recentActivity as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td class="text-muted">{{ $u->email }}</td>
                            <td class="text-muted">{{ \Carbon\Carbon::parse($u->created_at)->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
    @endif
</div>
@endsection
