@extends('layouts.app')
@section('title', 'New Journal Entry')

@section('content')
<div class="row">
    <div class="col-lg-10">
        <div class="card shadow-sm"><div class="card-body">
            <form method="POST" action="{{ route('accounting.journals.store') }}" id="journalForm">
                @csrf

                {{-- Entry header --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Entry Date <span class="text-danger">*</span></label>
                        <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}"
                               class="form-control @error('entry_date') is-invalid @enderror" required>
                        @error('entry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            @foreach (['journal', 'income', 'expense', 'transfer', 'opening'] as $t)
                                <option value="{{ $t }}" @selected(old('type', 'journal') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                            <option value="posted" @selected(old('status') === 'posted')>Post Immediately</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Narration</label>
                        <input type="text" name="narration" value="{{ old('narration') }}"
                               class="form-control @error('narration') is-invalid @enderror" maxlength="500"
                               placeholder="Brief description of this transaction">
                        @error('narration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Lines --}}
                @if ($errors->has('lines'))
                    <div class="alert alert-danger">{{ $errors->first('lines') }}</div>
                @endif

                <h6 class="fw-semibold mb-2">Transaction Lines</h6>
                <div class="table-responsive mb-2">
                    <table class="table table-bordered align-middle" id="linesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40%">Account</th>
                                <th style="width:20%">Debit (₹)</th>
                                <th style="width:20%">Credit (₹)</th>
                                <th style="width:15%">Memo</th>
                                <th style="width:5%"></th>
                            </tr>
                        </thead>
                        <tbody id="linesBody">
                        @php $oldLines = old('lines', [['ledger_account_id'=>'','debit'=>'','credit'=>'','memo'=>''],['ledger_account_id'=>'','debit'=>'','credit'=>'','memo'=>'']]); @endphp
                        @foreach ($oldLines as $i => $line)
                            <tr class="line-row">
                                <td>
                                    <select name="lines[{{ $i }}][ledger_account_id]" class="form-select form-select-sm account-select" required>
                                        <option value="">— Select Account —</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}" @selected((int)($line['ledger_account_id'] ?? 0) === $account->id)>
                                                {{ $account->code ? '[' . $account->code . '] ' : '' }}{{ $account->name }} ({{ ucfirst($account->type) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="lines[{{ $i }}][debit]" step="0.01" min="0"
                                           value="{{ $line['debit'] ?? '' }}"
                                           class="form-control form-control-sm debit-input text-end" placeholder="0.00">
                                </td>
                                <td>
                                    <input type="number" name="lines[{{ $i }}][credit]" step="0.01" min="0"
                                           value="{{ $line['credit'] ?? '' }}"
                                           class="form-control form-control-sm credit-input text-end" placeholder="0.00">
                                </td>
                                <td>
                                    <input type="text" name="lines[{{ $i }}][memo]"
                                           value="{{ $line['memo'] ?? '' }}"
                                           class="form-control form-control-sm" maxlength="255">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-semibold">
                                <td>Totals</td>
                                <td class="text-end" id="totalDebit">₹0.00</td>
                                <td class="text-end" id="totalCredit">₹0.00</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button type="button" id="addLine" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="bi bi-plus-lg"></i> Add Line
                </button>

                <div id="balanceWarning" class="alert alert-warning d-none">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Entry is not balanced. Debits and credits must be equal.
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Entry</button>
                    <a href="{{ route('accounting.journals.index') }}" class="btn btn-link">Cancel</a>
                </div>
            </form>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function ($) {
    'use strict';

    // Template for a new line row
    function newLineRow(idx) {
        const accounts = @json($accounts->map(fn($a) => ['id' => $a->id, 'label' => ($a->code ? '[' . $a->code . '] ' : '') . $a->name . ' (' . ucfirst($a->type) . ')']));
        let opts = '<option value="">— Select Account —</option>';
        accounts.forEach(function (a) {
            opts += `<option value="${a.id}">${a.label}</option>`;
        });

        return `<tr class="line-row">
            <td><select name="lines[${idx}][ledger_account_id]" class="form-select form-select-sm account-select" required>${opts}</select></td>
            <td><input type="number" name="lines[${idx}][debit]" step="0.01" min="0" value="" class="form-control form-control-sm debit-input text-end" placeholder="0.00"></td>
            <td><input type="number" name="lines[${idx}][credit]" step="0.01" min="0" value="" class="form-control form-control-sm credit-input text-end" placeholder="0.00"></td>
            <td><input type="text" name="lines[${idx}][memo]" value="" class="form-control form-control-sm" maxlength="255"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove"><i class="bi bi-x"></i></button></td>
        </tr>`;
    }

    function reindex() {
        $('#linesBody tr.line-row').each(function (i) {
            $(this).find('[name]').each(function () {
                $(this).attr('name', $(this).attr('name').replace(/lines\[\d+\]/, `lines[${i}]`));
            });
        });
    }

    function updateTotals() {
        let debit = 0, credit = 0;
        $('.debit-input').each(function () { debit += parseFloat($(this).val() || 0); });
        $('.credit-input').each(function () { credit += parseFloat($(this).val() || 0); });

        $('#totalDebit').text('₹' + debit.toFixed(2));
        $('#totalCredit').text('₹' + credit.toFixed(2));

        const balanced = Math.abs(debit - credit) < 0.001 && debit > 0;
        $('#balanceWarning').toggleClass('d-none', balanced);
        $('#totalDebit, #totalCredit').toggleClass('text-danger', !balanced).toggleClass('text-success', balanced);
    }

    $(document).on('input', '.debit-input, .credit-input', updateTotals);

    $('#addLine').on('click', function () {
        const idx = $('#linesBody tr.line-row').length;
        $('#linesBody').append(newLineRow(idx));
        updateTotals();
    });

    $(document).on('click', '.remove-line', function () {
        if ($('#linesBody tr.line-row').length <= 2) {
            alert('A journal entry requires at least two lines.');
            return;
        }
        $(this).closest('tr').remove();
        reindex();
        updateTotals();
    });

    updateTotals();
})(jQuery);
</script>
@endpush
