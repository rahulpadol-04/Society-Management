@extends('layouts.app')
@section('title', $plan->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('plans.index') }}">Plans</a></li>
    <li class="breadcrumb-item active">{{ $plan->name }}</li>
@endsection

@section('page-actions')
    @can('update', $plan)
        <a href="{{ route('plans.edit', $plan) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Edit
        </a>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm h-100"><div class="card-body">
            <h2 class="h5">{{ $plan->name }}</h2>
            <div class="display-5 fw-bold mb-1">{{ money($plan->price) }}</div>
            <div class="text-muted text-capitalize mb-3">{{ $plan->billing_cycle }}</div>

            @if ($plan->description)
                <p class="small">{{ $plan->description }}</p>
            @endif

            <dl class="row small">
                <dt class="col-6">Trial Days</dt><dd class="col-6">{{ $plan->trial_days ?? 0 }}</dd>
                <dt class="col-6">Max Units</dt><dd class="col-6">{{ $plan->max_units ?? '∞' }}</dd>
                <dt class="col-6">Max Users</dt><dd class="col-6">{{ $plan->max_users ?? '∞' }}</dd>
                <dt class="col-6">Storage</dt><dd class="col-6">{{ $plan->max_storage_mb ? $plan->max_storage_mb.' MB' : '∞' }}</dd>
                <dt class="col-6">Societies</dt><dd class="col-6">{{ $plan->societies_count }}</dd>
                <dt class="col-6">Status</dt>
                <dd class="col-6">
                    <span class="badge text-bg-{{ $plan->is_active ? 'success' : 'secondary' }}">
                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </dd>
            </dl>
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Included Features</h2>
            @if ($plan->features)
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($plan->features as $feat)
                        <span class="badge text-bg-primary fs-6">{{ $feat }}</span>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">No features defined.</p>
            @endif
        </div></div>
    </div>
</div>
@endsection
