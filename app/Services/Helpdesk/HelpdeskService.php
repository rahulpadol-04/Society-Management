<?php

declare(strict_types=1);

namespace App\Services\Helpdesk;

use App\Events\Helpdesk\TicketAssigned;
use App\Events\Helpdesk\TicketCreated;
use App\Events\Helpdesk\TicketStatusChanged;
use App\Models\SupportTicket;
use App\Models\TicketActivity;
use App\Models\TicketReply;
use App\Repositories\Contracts\SupportTicketRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Encapsulates the helpdesk ticket lifecycle: creation (with TKT reference and
 * SLA computation), reply, assignment, status transitions, escalation and
 * timeline logging. Side effects (notifications) are fired via domain events.
 */
class HelpdeskService extends BaseService
{
    public function __construct(SupportTicketRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function statusCounts(): array
    {
        return $this->repository->statusCounts();
    }

    public function create(array $data): SupportTicket
    {
        return DB::transaction(function () use ($data) {
            /** @var SupportTicket $ticket */
            $ticket = $this->repository->create([
                ...$data,
                'ticket_number' => $this->generateTicketNumber(),
                'raised_by'     => $data['raised_by'] ?? auth()->id(),
                'status'        => 'open',
                'sla_due_at'    => now()->addHours(48),
            ]);

            $this->log($ticket, 'created', note: 'Ticket registered');

            TicketCreated::dispatch($ticket);

            return $ticket;
        });
    }

    public function reply(SupportTicket $ticket, string $message, bool $isInternal = false, ?int $userId = null): TicketReply
    {
        return DB::transaction(function () use ($ticket, $message, $isInternal, $userId) {
            $reply = TicketReply::create([
                'society_id'       => $ticket->society_id,
                'support_ticket_id' => $ticket->id,
                'user_id'          => $userId ?? auth()->id(),
                'message'          => $message,
                'is_internal'      => $isInternal,
            ]);

            $this->log($ticket, $isInternal ? 'internal_note' : 'replied', note: Str::limit($message, 100));

            return $reply;
        });
    }

    public function assign(SupportTicket $ticket, int $userId, ?string $note = null): SupportTicket
    {
        return DB::transaction(function () use ($ticket, $userId, $note) {
            $ticket->update([
                'assigned_to' => $userId,
                'status'      => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
            ]);

            $this->log($ticket, 'assigned', note: $note ?? 'Assigned to staff');

            TicketAssigned::dispatch($ticket);

            return $ticket->refresh();
        });
    }

    public function changeStatus(SupportTicket $ticket, string $status, ?string $note = null): SupportTicket
    {
        return DB::transaction(function () use ($ticket, $status, $note) {
            $from = $ticket->status;

            $ticket->update([
                'status'      => $status,
                'resolved_at' => $status === 'resolved' ? now() : $ticket->resolved_at,
                'closed_at'   => $status === 'closed'   ? now() : $ticket->closed_at,
            ]);

            $this->log($ticket, 'status_changed', $from, $status, $note);

            TicketStatusChanged::dispatch($ticket, $from, $status);

            return $ticket->refresh();
        });
    }

    public function escalate(SupportTicket $ticket, ?string $note = null): SupportTicket
    {
        return DB::transaction(function () use ($ticket, $note) {
            $ticket->increment('escalation_level');

            $this->log($ticket, 'escalated', note: $note ?? "Escalated to level {$ticket->escalation_level}");

            return $ticket->refresh();
        });
    }

    protected function log(
        SupportTicket $ticket,
        string        $action,
        ?string       $from = null,
        ?string       $to = null,
        ?string       $note = null,
    ): void {
        TicketActivity::create([
            'society_id'        => $ticket->society_id,
            'support_ticket_id' => $ticket->id,
            'user_id'           => auth()->id(),
            'action'            => $action,
            'from_status'       => $from,
            'to_status'         => $to,
            'note'              => $note,
        ]);
    }

    protected function generateTicketNumber(): string
    {
        do {
            $ref = 'TKT-'.now()->format('ym').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (SupportTicket::withTrashed()->where('ticket_number', $ref)->exists());

        return $ref;
    }
}
