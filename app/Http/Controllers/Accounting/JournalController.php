<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreJournalEntryRequest;
use App\Models\JournalEntry;
use App\Models\LedgerAccount;
use App\Services\Accounting\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function __construct(protected AccountingService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', JournalEntry::class);

        $entries = JournalEntry::with(['creator'])
            ->latest()
            ->limit(500)
            ->get();

        return view('accounting.journals.index', [
            'entries'      => $entries,
            'statusCounts' => $this->service->repository()->statusCounts(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', JournalEntry::class);

        $accounts = LedgerAccount::active()->orderBy('type')->orderBy('name')->get();

        return view('accounting.journals.create', compact('accounts'));
    }

    public function store(StoreJournalEntryRequest $request): RedirectResponse
    {
        $data  = $request->safe()->except('lines');
        $lines = $request->validated('lines');

        try {
            $entry = $this->service->createEntry($data, $lines);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('accounting.journals.show', $entry)
            ->with('success', "Journal entry {$entry->reference} created.");
    }

    public function show(JournalEntry $journal): View
    {
        $this->authorize('view', $journal);

        $journal->load(['lines.account', 'creator', 'poster']);

        return view('accounting.journals.show', compact('journal'));
    }

    public function post(Request $request, JournalEntry $journal): RedirectResponse
    {
        $this->authorize('post', $journal);

        try {
            $this->service->post($journal, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('accounting.journals.show', $journal)
            ->with('success', "Entry {$journal->reference} posted.");
    }

    public function destroy(JournalEntry $journal): RedirectResponse
    {
        $this->authorize('delete', $journal);

        $journal->delete();

        return redirect()->route('accounting.journals.index')
            ->with('success', 'Journal entry deleted.');
    }
}
