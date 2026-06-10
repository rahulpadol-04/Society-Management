<?php

declare(strict_types=1);

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreBroadcastRequest;
use App\Models\Broadcast;
use App\Models\MessageTemplate;
use App\Services\Communication\CommunicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastController extends Controller
{
    public function __construct(protected CommunicationService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Broadcast::class);

        $broadcasts = $this->service->paginate(
            $request->only(['status', 'audience', 'search', 'sort', 'dir', 'per_page']),
            ['creator'],
        );

        return view('communication.broadcasts.index', compact('broadcasts'));
    }

    public function create(): View
    {
        $this->authorize('create', Broadcast::class);

        $templates = MessageTemplate::active()->get(['id', 'name', 'channel', 'subject', 'body']);

        return view('communication.broadcasts.create', compact('templates'));
    }

    public function store(StoreBroadcastRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['status']     = 'draft';

        $broadcast = $this->service->create($data);

        return redirect()->route('communication.broadcasts.show', $broadcast)
            ->with('success', 'Broadcast created. Review and click Send when ready.');
    }

    public function show(Broadcast $broadcast): View
    {
        $this->authorize('view', $broadcast);

        $broadcast->load(['creator', 'recipients.recipient']);

        return view('communication.broadcasts.show', compact('broadcast'));
    }

    public function send(Request $request, Broadcast $broadcast): RedirectResponse
    {
        $this->authorize('send', $broadcast);

        if (! in_array($broadcast->status, ['draft', 'failed'], true)) {
            return back()->with('error', 'Broadcast has already been dispatched.');
        }

        $this->service->sendBroadcast($broadcast);

        return redirect()->route('communication.broadcasts.show', $broadcast)
            ->with('success', 'Broadcast queued for delivery.');
    }
}
