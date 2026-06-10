<?php

declare(strict_types=1);

namespace App\Jobs\Communication;

use App\Models\Broadcast;
use App\Models\BroadcastRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Simulates multi-channel delivery for a broadcast. In production, each
 * recipient row would invoke the appropriate gateway (SMTP, SMS API, etc.).
 * Here we simply mark records as sent, demonstrating the tenancy + queue pattern
 * established by GenerateMonthlyBills.
 */
class DeliverBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $broadcastId) {}

    public function handle(): void
    {
        $broadcast = Broadcast::withoutGlobalScopes()->find($this->broadcastId);

        if (! $broadcast) {
            return;
        }

        // Bind tenant context so scoped models work correctly inside the job.
        app('tenancy')->set($broadcast->society);

        try {
            BroadcastRecipient::withoutGlobalScopes()
                ->where('broadcast_id', $broadcast->id)
                ->where('status', 'pending')
                ->each(function (BroadcastRecipient $recipient): void {
                    // Simulate delivery — no real gateway in this scaffold.
                    $recipient->update([
                        'status'  => 'sent',
                        'sent_at' => now(),
                    ]);
                });

            $broadcast->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);
        } finally {
            app('tenancy')->forget();
        }
    }
}
