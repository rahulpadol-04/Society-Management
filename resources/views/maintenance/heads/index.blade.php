@extends('layouts.app')
@section('title', 'Maintenance Heads')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('maintenance.index') }}">Maintenance Billing</a></li>
    <li class="breadcrumb-item active">Charge Heads</li>
@endsection

@section('page-actions')
    @can('create', App\Models\MaintenanceHead::class)
        <a href="{{ route('maintenance.heads.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Head
        </a>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Name</th><th>Code</th><th>Type</th><th>Amount</th>
                    <th>Frequency</th><th>Taxable</th><th>GST %</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($heads as $head)
                <tr>
                    <td class="fw-semibold">{{ $head->name }}</td>
                    <td>{{ $head->code ?? '—' }}</td>
                    <td><span class="badge text-bg-light text-capitalize">{{ str_replace('_', ' ', $head->type) }}</span></td>
                    <td>{{ money($head->amount) }}</td>
                    <td class="text-capitalize">{{ str_replace('_', ' ', $head->frequency) }}</td>
                    <td>
                        @if ($head->is_taxable)
                            <span class="badge text-bg-success">Yes</span>
                        @else
                            <span class="badge text-bg-secondary">No</span>
                        @endif
                    </td>
                    <td>{{ $head->is_taxable ? ($head->gst_percentage ?? config('communityos.billing.gst_percentage')).'%' : '—' }}</td>
                    <td>
                        @if ($head->is_active)
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @can('update', $head)
                            <a href="{{ route('maintenance.heads.edit', $head) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endcan
                        @can('delete', $head)
                            <form method="POST" action="{{ route('maintenance.heads.destroy', $head) }}"
                                  class="d-inline" data-confirm="Delete this maintenance head?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No maintenance heads defined yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
