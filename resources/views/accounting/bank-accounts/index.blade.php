@extends('layouts.app')
@section('title', 'Bank Accounts')

@section('page-actions')
    @can('create', App\Models\BankAccount::class)
        <a href="{{ route('accounting.bank.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Account
        </a>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Bank Name</th>
                    <th>Account No.</th>
                    <th>Opening Balance</th>
                    <th>Current Balance</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($accounts as $account)
                <tr>
                    <td class="fw-semibold">{{ $account->name }}</td>
                    <td><span class="badge text-bg-light text-capitalize">{{ $account->account_type }}</span></td>
                    <td class="text-muted small">{{ $account->bank_name ?? '—' }}</td>
                    <td class="text-muted small">{{ $account->account_number ?? '—' }}</td>
                    <td>{{ money($account->opening_balance) }}</td>
                    <td class="fw-semibold">{{ money($account->current_balance) }}</td>
                    <td>
                        @if ($account->is_active)
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end d-flex gap-1 justify-content-end">
                        @can('update', $account)
                            <a href="{{ route('accounting.bank.edit', $account) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endcan
                        @can('delete', $account)
                            <form method="POST" action="{{ route('accounting.bank.destroy', $account) }}"
                                  data-confirm="Delete this bank account?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No bank accounts defined yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
