<?php

declare(strict_types=1);

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\BillGenerated;
use App\Events\Maintenance\PaymentReceived;
use App\Models\User;
use App\Notifications\Maintenance\BillingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Queued listener (auto-discovered) that notifies the resident whenever a bill
 * is generated or a payment is recorded. Mirrors the complaint notification pattern.
 */
class NotifyBillingParties implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(BillGenerated|PaymentReceived $event): void
    {
        $bill = $event->bill->loadMissing('resident');

        [$recipients, $action] = match (true) {
            $event instanceof BillGenerated  => [$this->billRecipients($bill), 'generated'],
            $event instanceof PaymentReceived => [collect([$bill->resident]), 'payment'],
        };

        $recipients = $recipients->filter()->unique('id');

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new BillingNotification($bill, $action));
        }
    }

    protected function billRecipients($bill): Collection
    {
        $recipients = collect();

        if ($bill->resident) {
            $recipients->push($bill->resident);
        }

        return $recipients;
    }
}
