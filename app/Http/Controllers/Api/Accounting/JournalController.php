<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreJournalEntryRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use App\Services\Accounting\AccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JournalController extends Controller
{
    use ApiResponse;

    public function __construct(protected AccountingService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', JournalEntry::class);

        $entries = $this->service->paginate(
            $request->only(['status', 'type', 'search', 'sort', 'dir', 'per_page']),
            ['creator', 'lines.account'],
        );

        return $this->paginated(
            $entries->setCollection(
                $entries->getCollection()->map(fn ($e) => (new JournalEntryResource($e))->resolve())
            )
        );
    }

    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        $data  = $request->safe()->except('lines');
        $lines = $request->validated('lines');

        try {
            $entry = $this->service->createEntry($data, $lines);
        } catch (ValidationException $e) {
            return $this->fail($e->getMessage(), 422, $e->errors());
        }

        return $this->created(new JournalEntryResource($entry->load(['lines.account', 'creator'])), "Entry {$entry->reference} created.");
    }

    public function show(JournalEntry $journal): JsonResponse
    {
        $this->authorize('view', $journal);

        return $this->ok(new JournalEntryResource($journal->load(['lines.account', 'creator', 'poster'])));
    }
}
