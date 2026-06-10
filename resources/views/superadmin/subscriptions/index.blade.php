@extends('layouts.app')
@section('title', 'Subscriptions')

@section('content')

{{-- Status filter tabs --}}
<div class="mb-3">
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('subscriptions.index') }}"
           class="btn btn-sm {{ ! request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
            All ({{ $statusCounts->sum() }})
        </a>
        @foreach (['trial','active','past_due','cancelled','expired'] as $st)
            <a href="{{ route('subscriptions.index', ['status' => $st]) }}"
               class="btn btn-sm {{ request('status') === $st ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ ucfirst(str_replace('_', ' ', $st)) }} ({{ $statusCounts[$st] ?? 0 }})
            </a>
        @endforeach
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Society</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Cycle</th>
                        <th>Starts</th>
                        <th>Ends</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($subscriptions as $sub)
                    <tr>
                        <td>
                            <a href="{{ route('societies.show', $sub->society) }}">{{ $sub->society?->name ?? '—' }}</a>
                        </td>
                        <td>{{ $sub->plan?->name ?? '—' }}</td>
                        <td>
                            <span class="badge status-{{ $sub->status }} text-capitalize">
                                {{ str_replace('_', ' ', $sub->status) }}
                            </span>
                        </td>
                        <td>{{ money($sub->amount) }}</td>
                        <td class="text-capitalize">{{ $sub->billing_cycle }}</td>
                        <td>{{ $sub->starts_at?->format('d M Y') ?? '—' }}</td>
                        <td>{{ $sub->ends_at?->format('d M Y') ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('subscriptions.show', $sub) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No subscriptions found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $subscriptions->links() }}
    </div>
</div>
@endsection
