<?php

declare(strict_types=1);

namespace App\Jobs\Maintenance;

use App\Models\Society;
use App\Services\Maintenance\BillingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Iterates every society and generates maintenance bills for the current
 * calendar month. Scheduled to run on the 1st of each month at 02:00.
 */
class GenerateMonthlyBills implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $period = now()->format('Y-m');

        Society::all()->each(function (Society $society) use ($period): void {
            app('tenancy')->set($society);

            try {
                app(BillingService::class)->generateBillsForPeriod($period);
            } finally {
                app('tenancy')->forget();
            }
        });
    }
}
