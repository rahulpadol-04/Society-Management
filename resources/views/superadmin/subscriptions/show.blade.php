@extends('layouts.app')
@section('title', 'Subscription #'.$subscription->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('subscriptions.index') }}">Subscriptions</a></li>
    <li class="breadcrumb-item active">#{{ $subscription->id }}</li>
@endsection

@section('page-actions')
    @if (! in_array($subscription->status, ['cancelled', 'expired']))
        @can('cancel', $subscription)
            <form method="POST" action="{{ route('subscriptions.cancel', $subscription) }}"
                  data-confirm="Cancel this subscription?">
                @csrf
                <button class="btn btn-outline-danger">
                    <i class="bi bi-x-circle"></i> Cancel Subscription
                </button>
            </form>
        @endcan
    @endif
@endsection

@section('content')
<div class="row g-3">
    <div class="col-md-5">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Subscription Details</h2>
            <dl class="row small mb-0">
                <dt class="col-5 text-muted">Society</dt>
                <dd class="col-7">
                    <a href="{{ route('societies.show', $subscription->society) }}">{{ $subscription->society?->name }}</a>
                </dd>
                <dt class="col-5 text-muted">Plan</dt>
                <dd class="col-7">{{ $subscription->plan?->name ?? '—' }}</dd>
                <dt class="col-5 text-muted">Status</dt>
                <dd class="col-7">
                    <span class="badge status-{{ $subscription->status }} text-capitalize">
                        {{ str_replace('_', ' ', $subscription->status) }}
                    </span>
                </dd>
                <dt class="col-5 text-muted">Amount</dt>
                <dd class="col-7">{{ money($subscription->amount) }}</dd>
                <dt class="col-5 text-muted">Cycle</dt>
                <dd class="col-7 text-capitalize">{{ $subscription->billing_cycle }}</dd>
                <dt class="col-5 text-muted">Starts</dt>
                <dd class="col-7">{{ $subscription->starts_at?->format('d M Y') ?? '—' }}</dd>
                <dt class="col-5 text-muted">Ends</dt>
                <dd class="col-7">{{ $subscription->ends_at?->format('d M Y') ?? '—' }}</dd>
                @if ($subscription->cancelled_at)
                    <dt class="col-5 text-muted">Cancelled</dt>
                    <dd class="col-7">{{ $subscription->cancelled_at->format('d M Y') }}</dd>
                @endif
            </dl>
        </div></div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Invoices</h2>
            @forelse ($invoices as $inv)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2 small">
                    <div>
                        <span class="fw-semibold">{{ $inv->invoice_number }}</span>
                        <span class="text-muted ms-2">{{ $inv->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge status-{{ $inv->status }} text-capitalize">{{ $inv->status }}</span>
                        <span class="fw-semibold">{{ money($inv->total) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0">No invoices yet.</p>
            @endforelse
        </div></div>
    </div>
</div>
@endsection
