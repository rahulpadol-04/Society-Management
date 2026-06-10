<?php

declare(strict_types=1);

namespace App\Jobs\Maintenance;

use App\Models\MaintenanceBill;
use App\Models\Society;
use App\Notifications\Maintenance\BillingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

/**
 * Sends due-date reminder notifications to residents whose maintenance bills
 * are unpaid and due within the next 3 days. Scheduled to run daily at 09:00.
 */
class SendDueReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Society::all()->each(function (Society $society): void {
            app('tenancy')->set($society);

            try {
                $bills = MaintenanceBill::whereIn('status', ['unpaid', 'partial'])
                    ->whereBetween('due_date', [now()->toDateString(), now()->addDays(3)->toDateString()])
                    ->with('resident')
                    ->get();

                foreach ($bills as $bill) {
                    if ($bill->resident) {
                        Notification::send(
                            collect([$bill->resident]),
                            new BillingNotification($bill, 'due_soon')
                        );
                    }
                }
            } finally {
                app('tenancy')->forget();
            }
        });
    }
}
