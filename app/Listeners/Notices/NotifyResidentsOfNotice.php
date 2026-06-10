<?php

declare(strict_types=1);

namespace App\Listeners\Notices;

use App\Events\Notices\NoticePublished;
use App\Models\User;
use App\Notifications\Notices\NoticeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Queued listener that notifies relevant residents/tenants when a notice is
 * published. Audience filtering ensures only the right people are notified.
 */
class NotifyResidentsOfNotice implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(NoticePublished $event): void
    {
        $notice = $event->notice;

        $roles = match ($notice->audience) {
            'owners'  => ['resident'],
            'tenants' => ['tenant'],
            'staff'   => ['security-guard', 'maintenance-staff'],
            default   => ['resident', 'tenant'],
        };

        $recipients = User::withoutGlobalScopes()
            ->where('society_id', $notice->society_id)
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', $roles))
            ->get()
            ->filter()
            ->unique('id');

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NoticeNotification($notice));
        }
    }
}
