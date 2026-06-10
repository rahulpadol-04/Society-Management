@extends('layouts.app')
@section('title', 'Journal Entry — '.$journal->reference)

@section('page-actions')
    @if ($journal->status === 'draft')
        @can('post', $journal)
            <form method="POST" action="{{ route('accounting.journals.post', $journal) }}" class="d-inline"
                  data-confirm="Post this entry? It cannot be edited afterwards.">
                @csrf
                <button class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Post Entry</button>
            </form>
        @endcan
        @can('delete', $journal)
            <form method="POST" action="{{ route('accounting.journals.destroy', $journal) }}" class="d-inline"
                  data-confirm="Delete this draft entry?">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
        @endcan
    @endif
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $journal->reference }}</span>
                <span class="badge {{ $journal->status === 'posted' ? 'text-bg-success' : 'text-bg-secondary' }} fs-6">
                    {{ ucfirst($journal->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row small text-muted mb-3">
                    <div class="col-sm-4"><strong>Date:</strong> {{ $journal->entry_date->format('d M Y') }}</div>
                    <div class="col-sm-4"><strong>Type:</strong> {{ ucfirst($journal->type) }}</div>
                    <div class="col-sm-4"><strong>Amount:</strong> {{ money($journal->amount) }}</div>
                    <div class="col-sm-4 mt-1"><strong>Source:</strong> {{ $journal->source ?? '—' }}</div>
                    <div class="col-sm-4 mt-1"><strong>Created By:</strong> {{ $journal->creator?->name ?? '—' }}</div>
                    @if ($journal->status === 'posted')
                        <div class="col-sm-4 mt-1"><strong>Posted By:</strong> {{ $journal->poster?->name ?? '—' }}</div>
                        <div class="col-sm-12 mt-1"><strong>Posted At:</strong> {{ $journal->posted_at?->format('d M Y H:i') }}</div>
                    @endif
                </div>

                @if ($journal->narration)
                    <p class="text-muted mb-3">{{ $journal->narration }}</p>
                @endif

                {{-- Lines --}}
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th>Memo</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($journal->lines as $line)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $line->account?->name ?? '—' }}</div>
                                    <div class="text-muted small text-capitalize">{{ $line->account?->type }}</div>
                                </td>
                                <td class="text-end fw-semibold {{ $line->debit > 0 ? 'text-primary' : 'text-muted' }}">
                                    {{ $line->debit > 0 ? money($line->debit) : '—' }}
                                </td>
                                <td class="text-end fw-semibold {{ $line->credit > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $line->credit > 0 ? money($line->credit) : '—' }}
                                </td>
                                <td class="text-muted small">{{ $line->memo ?? '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Total</td>
                                <td class="text-end">{{ money($journal->totalDebit()) }}</td>
                                <td class="text-end">{{ money($journal->totalCredit()) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if (! $journal->isBalanced())
                    <div class="alert alert-danger mt-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Warning: This entry is <strong>not balanced</strong>.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Quick Actions</div>
            <div class="list-group list-group-flush">
                <a href="{{ route('accounting.journals.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-arrow-left me-2"></i> Back to Journals
                </a>
                <a href="{{ route('accounting.reports.trial') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i> Trial Balance
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
