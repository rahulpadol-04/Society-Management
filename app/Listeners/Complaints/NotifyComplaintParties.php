<?php

declare(strict_types=1);

namespace App\Listeners\Complaints;

use App\Events\Complaints\ComplaintAssigned;
use App\Events\Complaints\ComplaintCreated;
use App\Events\Complaints\ComplaintStatusChanged;
use App\Models\User;
use App\Notifications\Complaints\ComplaintNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Queued listener (auto-discovered) that notifies the relevant people whenever
 * a complaint is created, assigned or changes status. Demonstrates the
 * event -> queued listener -> multi-channel notification pattern modules reuse.
 */
class NotifyComplaintParties implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(ComplaintCreated|ComplaintAssigned|ComplaintStatusChanged $event): void
    {
        $complaint = $event->complaint->loadMissing('raisedBy', 'assignee');

        [$recipients, $action] = match (true) {
            $event instanceof ComplaintCreated  => [$this->societyManagers($complaint->society_id), 'created'],
            $event instanceof ComplaintAssigned => [collect([$complaint->assignee, $complaint->raisedBy]), 'assigned'],
            default                             => [collect([$complaint->raisedBy]), 'status_changed'],
        };

        $recipients = $recipients->filter()->unique('id');

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new ComplaintNotification($complaint, $action));
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
