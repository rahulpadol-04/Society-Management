@extends('layouts.app')
@section('title', 'Accounting')

@section('page-actions')
    @can('create', App\Models\JournalEntry::class)
        <a href="{{ route('accounting.journals.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Journal Entry
        </a>
    @endcan
@endsection

@section('content')
{{-- KPI stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success rounded-3 p-3">
                    <i class="bi bi-arrow-down-circle fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Income</div>
                    <div class="h5 mb-0 fw-bold">{{ money($totalIncome) }}</div>
                    <div class="text-muted" style="font-size:.75rem">This month</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger-subtle text-danger rounded-3 p-3">
                    <i class="bi bi-arrow-up-circle fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Expenses</div>
                    <div class="h5 mb-0 fw-bold">{{ money($totalExpense) }}</div>
                    <div class="text-muted" style="font-size:.75rem">This month</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary rounded-3 p-3">
                    <i class="bi bi-graph-up-arrow fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Net Surplus</div>
                    <div class="h5 mb-0 fw-bold {{ $surplus >= 0 ? 'text-success' : 'text-danger' }}">{{ money($surplus) }}</div>
                    <div class="text-muted" style="font-size:.75rem">This month</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info rounded-3 p-3">
                    <i class="bi bi-bank fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Bank Balance</div>
                    <div class="h5 mb-0 fw-bold">{{ money($bankBalance) }}</div>
                    <div class="text-muted" style="font-size:.75rem">All accounts</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Recent journal entries --}}
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Recent Journal Entries</span>
                <a href="{{ route('accounting.journals.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Narration</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($recentEntries as $entry)
                            <tr>
                                <td><a href="{{ route('accounting.journals.show', $entry) }}" class="fw-semibold">{{ $entry->reference }}</a></td>
                                <td class="text-muted small">{{ $entry->entry_date->format('d M Y') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($entry->narration ?? '—', 40) }}</td>
                                <td>{{ money($entry->amount) }}</td>
                                <td>
                                    <span class="badge {{ $entry->status === 'posted' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($entry->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('accounting.journals.show', $entry) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No journal entries yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Quick Links</div>
            <div class="list-group list-group-flush">
                <a href="{{ route('accounting.accounts.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-list-columns me-2 text-primary"></i> Chart of Accounts
                </a>
                <a href="{{ route('accounting.journals.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-journal-text me-2 text-info"></i> Journal Entries
                </a>
                <a href="{{ route('accounting.bank.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-bank me-2 text-warning"></i> Bank Accounts
                </a>
                @can('accounting.reports')
                <a href="{{ route('accounting.reports.trial') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-file-earmark-bar-graph me-2 text-success"></i> Trial Balance
                </a>
                <a href="{{ route('accounting.reports.pl') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-bar-chart-line me-2 text-danger"></i> Profit &amp; Loss
                </a>
                <a href="{{ route('accounting.reports.bs') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-clipboard-data me-2 text-secondary"></i> Balance Sheet
                </a>
                @endcan
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Entry Status</div>
            <div class="card-body">
                @foreach (['draft' => 'secondary', 'posted' => 'success'] as $status => $color)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-capitalize">{{ $status }}</span>
                        <span class="badge text-bg-{{ $color }}">{{ $statusCounts[$status] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
