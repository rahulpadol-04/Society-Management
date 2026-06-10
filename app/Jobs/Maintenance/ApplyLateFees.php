<?php

declare(strict_types=1);

namespace App\Jobs\Maintenance;

use App\Models\MaintenanceBill;
use App\Models\Society;
use App\Services\Maintenance\BillingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * For each society, marks overdue bills and applies late fees to bills that
 * have passed the grace period. Scheduled to run nightly at 03:00.
 */
class ApplyLateFees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $graceDays = (int) config('communityos.billing.late_fee_grace_days', 10);

        Society::all()->each(function (Society $society) use ($graceDays): void {
            app('tenancy')->set($society);

            try {
                $service = app(BillingService::class);

                // First mark all past-due bills as overdue.
                $service->markOverdue();

                // Then apply late fees to bills overdue past the grace period.
                MaintenanceBill::whereIn('status', ['overdue'])
                    ->whereDate('due_date', '<', now()->subDays($graceDays))
                    ->get()
                    ->each(fn (MaintenanceBill $bill) => $service->applyLateFee($bill));
            } finally {
                app('tenancy')->forget();
            }
        });
    }
}
