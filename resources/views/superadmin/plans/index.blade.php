@extends('layouts.app')
@section('title', 'Subscription Plans')

@section('page-actions')
    @can('create', App\Models\SubscriptionPlan::class)
        <a href="{{ route('plans.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Plan
        </a>
    @endcan
@endsection

@section('content')
<div class="row g-3">
@forelse ($plans as $plan)
    <div class="col-md-6 col-xl-4">
        <div class="card shadow-sm h-100 {{ $plan->is_featured ? 'border-primary' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h2 class="h5 mb-0">{{ $plan->name }}</h2>
                        <span class="badge text-bg-{{ $plan->is_active ? 'success' : 'secondary' }} mt-1">
                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if ($plan->is_featured)
                            <span class="badge text-bg-primary ms-1">Featured</span>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="h4 mb-0">{{ money($plan->price) }}</div>
                        <div class="small text-muted text-capitalize">{{ $plan->billing_cycle }}</div>
                    </div>
                </div>

                @if ($plan->description)
                    <p class="small text-muted mb-2">{{ $plan->description }}</p>
                @endif

                <div class="row g-2 small mb-3">
                    <div class="col-4 text-center border-end">
                        <div class="fw-semibold">{{ $plan->max_units ?? '∞' }}</div>
                        <div class="text-muted">Units</div>
                    </div>
                    <div class="col-4 text-center border-end">
                        <div class="fw-semibold">{{ $plan->max_users ?? '∞' }}</div>
                        <div class="text-muted">Users</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="fw-semibold">{{ $plan->societies_count }}</div>
                        <div class="text-muted">Societies</div>
                    </div>
                </div>

                @if ($plan->features)
                    <div class="small mb-3">
                        @foreach ($plan->features as $feat)
                            <span class="badge text-bg-light me-1 mb-1">{{ $feat }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="d-flex gap-2">
                    @can('update', $plan)
                        <a href="{{ route('plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    @endcan
                    @can('delete', $plan)
                        <form method="POST" action="{{ route('plans.destroy', $plan) }}"
                              data-confirm="Delete plan &quot;{{ $plan->name }}&quot;?">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="alert alert-light border text-center">No subscription plans yet.</div>
    </div>
@endforelse
</div>
@endsection
