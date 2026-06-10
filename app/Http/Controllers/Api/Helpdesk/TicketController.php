<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Helpdesk;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\StoreReplyRequest;
use App\Http\Requests\Helpdesk\StoreTicketRequest;
use App\Http\Requests\Helpdesk\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\SupportTicket;
use App\Services\Helpdesk\HelpdeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use ApiResponse;

    public function __construct(protected HelpdeskService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SupportTicket::class);

        $tickets = $this->service->paginate(
            $request->only(['status', 'priority', 'category', 'assigned_to', 'raised_by', 'search', 'sort', 'dir', 'per_page']),
            ['raisedBy', 'assignee'],
        );

        return $this->paginated(
            $tickets->setCollection($tickets->getCollection()->map(fn ($t) => (new TicketResource($t))->resolve()))
        );
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->service->create($request->validated());

        return $this->created(new TicketResource($ticket), 'Ticket registered.');
    }

    public function show(SupportTicket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        return $this->ok(new TicketResource($ticket->load(['raisedBy', 'assignee', 'activities', 'replies'])));
    }

    public function update(UpdateTicketRequest $request, SupportTicket $ticket): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['assigned_to'])) {
            $this->service->assign($ticket, (int) $data['assigned_to'], $data['note'] ?? null);
        }
        if (! empty($data['status']) && $data['status'] !== $ticket->status) {
            $this->service->changeStatus($ticket, $data['status'], $data['note'] ?? null);
        }
        $ticket->update(collect($data)->only(['subject', 'description', 'category', 'priority'])->all());

        return $this->ok(new TicketResource($ticket->refresh()), 'Ticket updated.');
    }

    public function destroy(SupportTicket $ticket): JsonResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return $this->ok(null, 'Ticket deleted.');
    }

    public function reply(StoreReplyRequest $request, SupportTicket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $isInternal = $request->boolean('is_internal') && $request->user()->can('helpdesk.update');
        $replyModel = $this->service->reply($ticket, $request->validated('message'), $isInternal);

        return $this->created([
            'id'          => $replyModel->id,
            'message'     => $replyModel->message,
            'is_internal' => $replyModel->is_internal,
            'created_at'  => $replyModel->created_at,
        ], 'Reply posted.');
    }

    public function assign(Request $request, SupportTicket $ticket): JsonResponse
    {
        $this->authorize('assign', SupportTicket::class);

        $data = $request->validate([
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'note'        => ['nullable', 'string', 'max:500'],
        ]);

        $this->service->assign($ticket, (int) $data['assigned_to'], $data['note'] ?? null);

        return $this->ok(new TicketResource($ticket->refresh()), 'Ticket assigned.');
    }
}
