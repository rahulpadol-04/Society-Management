@extends('layouts.app')
@section('title', 'Chart of Accounts')

@section('page-actions')
    @can('create', App\Models\LedgerAccount::class)
        <a href="{{ route('accounting.accounts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Account
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
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Sub-type</th>
                        <th>Opening Balance</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($accounts as $account)
                    <tr>
                        <td class="text-muted small">{{ $account->code ?? '—' }}</td>
                        <td class="fw-semibold">{{ $account->name }}</td>
                        <td><span class="badge text-bg-light text-capitalize">{{ $account->type }}</span></td>
                        <td class="text-muted small">{{ $account->subtype ?? '—' }}</td>
                        <td>{{ money($account->opening_balance) }}</td>
                        <td>
                            @if ($account->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end d-flex gap-1 justify-content-end">
                            @can('update', $account)
                                <a href="{{ route('accounting.accounts.edit', $account) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endcan
                            @can('delete', $account)
                                <form method="POST" action="{{ route('accounting.accounts.destroy', $account) }}"
                                      data-confirm="Delete this ledger account?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No accounts defined yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
