<?php

declare(strict_types=1);

namespace App\Listeners\Helpdesk;

use App\Events\Helpdesk\TicketAssigned;
use App\Events\Helpdesk\TicketCreated;
use App\Events\Helpdesk\TicketStatusChanged;
use App\Models\User;
use App\Notifications\Helpdesk\TicketNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Queued listener (auto-discovered) that notifies the relevant parties whenever
 * a support ticket is created, assigned or changes status.
 */
class NotifyTicketParties implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(TicketCreated|TicketAssigned|TicketStatusChanged $event): void
    {
        $ticket = $event->ticket->loadMissing('raisedBy', 'assignee');

        [$recipients, $action] = match (true) {
            $event instanceof TicketCreated  => [$this->societyManagers($ticket->society_id), 'created'],
            $event instanceof TicketAssigned => [collect([$ticket->assignee, $ticket->raisedBy]), 'assigned'],
            default                          => [collect([$ticket->raisedBy]), 'status_changed'],
        };

        $recipients = $recipients->filter()->unique('id');

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new TicketNotification($ticket, $action));
        }
    }

    protected function societyManagers(int $societyId): Collection
    {
        return User::withoutGlobalScopes()
            ->where('society_id', $societyId)
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', ['society-admin', 'sub-admin']))
            ->get();
    }
}
