<?php

declare(strict_types=1);

namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\StoreReplyRequest;
use App\Http\Requests\Helpdesk\StoreTicketRequest;
use App\Http\Requests\Helpdesk\UpdateTicketRequest;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Helpdesk\HelpdeskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(protected HelpdeskService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $tickets = $this->service->repository()->query()
            ->with(['raisedBy', 'assignee'])
            ->latest()
            ->limit(1000)
            ->get();

        return view('helpdesk.index', [
            'tickets'      => $tickets,
            'statusCounts' => $this->service->statusCounts(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', SupportTicket::class);

        return view('helpdesk.create');
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $ticket = $this->service->create($request->validated());

        return redirect()->route('helpdesk.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} created.");
    }

    public function show(SupportTicket $helpdeskTicket): View
    {
        $this->authorize('view', $helpdeskTicket);

        $helpdeskTicket->load(['raisedBy', 'assignee', 'replies.author', 'activities.user']);

        return view('helpdesk.show', [
            'ticket' => $helpdeskTicket,
            'staff'  => $this->assignableStaff(),
        ]);
    }

    public function edit(SupportTicket $helpdeskTicket): View
    {
        $this->authorize('update', $helpdeskTicket);

        return view('helpdesk.edit', [
            'ticket' => $helpdeskTicket,
            'staff'  => $this->assignableStaff(),
        ]);
    }

    public function update(UpdateTicketRequest $request, SupportTicket $helpdeskTicket): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['assigned_to']) && (int) $data['assigned_to'] !== $helpdeskTicket->assigned_to) {
            $this->service->assign($helpdeskTicket, (int) $data['assigned_to'], $data['note'] ?? null);
        }

        if (! empty($data['status']) && $data['status'] !== $helpdeskTicket->status) {
            $this->service->changeStatus($helpdeskTicket, $data['status'], $data['note'] ?? null);
        }

        $helpdeskTicket->update(
            collect($data)->only(['subject', 'description', 'category', 'priority'])->all()
        );

        return redirect()->route('helpdesk.show', $helpdeskTicket)->with('success', 'Ticket updated.');
    }

    public function destroy(SupportTicket $helpdeskTicket): RedirectResponse
    {
        $this->authorize('delete', $helpdeskTicket);

        $helpdeskTicket->delete();

        return redirect()->route('helpdesk.index')->with('success', 'Ticket deleted.');
    }

    public function reply(StoreReplyRequest $request, SupportTicket $helpdeskTicket): RedirectResponse
    {
        $this->authorize('view', $helpdeskTicket);

        $isInternal = (bool) $request->boolean('is_internal') && $request->user()->can('helpdesk.update');

        $this->service->reply($helpdeskTicket, $request->validated('message'), $isInternal);

        return back()->with('success', $isInternal ? 'Internal note added.' : 'Reply posted.');
    }

    public function assign(Request $request, SupportTicket $helpdeskTicket): RedirectResponse
    {
        $this->authorize('assign', SupportTicket::class);

        $request->validate([
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'note'        => ['nullable', 'string', 'max:500'],
        ]);

        $this->service->assign($helpdeskTicket, (int) $request->assigned_to, $request->note);

        return back()->with('success', 'Ticket assigned.');
    }

    public function escalate(Request $request, SupportTicket $helpdeskTicket): RedirectResponse
    {
        $this->authorize('escalate', SupportTicket::class);

        $request->validate(['note' => ['nullable', 'string', 'max:500']]);

        $this->service->escalate($helpdeskTicket, $request->note);

        return back()->with('success', 'Ticket escalated.');
    }

    public function close(Request $request, SupportTicket $helpdeskTicket): RedirectResponse
    {
        $this->authorize('close', SupportTicket::class);

        $request->validate(['note' => ['nullable', 'string', 'max:500']]);

        $this->service->changeStatus($helpdeskTicket, 'closed', $request->note);

        return back()->with('success', 'Ticket closed.');
    }

    protected function assignableStaff()
    {
        return User::withoutGlobalScopes()
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', ['society-admin', 'sub-admin', 'maintenance-staff']))
            ->where('society_id', current_society_id())
            ->get();
    }
}
