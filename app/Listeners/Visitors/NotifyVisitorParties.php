<?php

declare(strict_types=1);

namespace App\Listeners\Visitors;

use App\Events\Visitors\VisitorApproved;
use App\Events\Visitors\VisitorCheckedIn;
use App\Events\Visitors\VisitorPassRequested;
use App\Models\User;
use App\Notifications\Visitors\VisitorNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Queued listener (auto-discovered) that notifies the relevant people whenever
 * a visitor pass is requested, approved, or a visitor checks in.
 */
class NotifyVisitorParties implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(VisitorPassRequested|VisitorApproved|VisitorCheckedIn $event): void
    {
        [$recipients, $action] = match (true) {
            $event instanceof VisitorPassRequested => [
                $this->societyManagers($event->pass->society_id),
                'requested',
            ],
            $event instanceof VisitorApproved => [
                $this->resolveHost($event->pass),
                'approved',
            ],
            default => [ // VisitorCheckedIn
                $this->resolveLogHost($event->log),
                'checked_in',
            ],
        };

        $recipients = $recipients->filter()->unique('id');

        if ($recipients->isNotEmpty()) {
            $subject = $event instanceof VisitorCheckedIn ? $event->log : $event->pass;
            Notification::send($recipients, new VisitorNotification($subject, $action));
        }
    }

    protected function societyManagers(int $societyId): Collection
    {
        return User::withoutGlobalScopes()
            ->where('society_id', $societyId)
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', ['society-admin', 'sub-admin']))
            ->get();
    }

    protected function resolveHost(\App\Models\VisitorPass $pass): Collection
    {
        if ($pass->host_id) {
            $host = User::withoutGlobalScopes()->find($pass->host_id);

            return $host ? collect([$host]) : collect();
        }

        return collect();
    }

    protected function resolveLogHost(\App\Models\VisitorLog $log): Collection
    {
        $log->loadMissing('pass');

        if ($log->pass && $log->pass->host_id) {
            $host = User::withoutGlobalScopes()->find($log->pass->host_id);

            return $host ? collect([$host]) : collect();
        }

        return collect();
    }
}
