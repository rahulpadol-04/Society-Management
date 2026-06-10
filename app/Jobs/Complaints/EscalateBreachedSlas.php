<?php

declare(strict_types=1);

namespace App\Jobs\Complaints;

use App\Models\Complaint;
use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

/**
 * Hourly cron job (scheduled in routes/console.php). Flags SLA-breached
 * complaints and helpdesk tickets across all tenants and bumps ticket
 * escalation levels. Runs cross-tenant (global scope suppressed) and is
 * idempotent — only un-flagged, still-open records are touched.
 */
class EscalateBreachedSlas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if (Schema::hasTable('complaints')) {
            Complaint::query()->withoutGlobalScopes()
                ->whereNotNull('sla_due_at')
                ->where('sla_due_at', '<', now())
                ->where('sla_breached', false)
                ->whereNotIn('status', ['resolved', 'closed'])
                ->update(['sla_breached' => true]);
        }

        if (Schema::hasTable('support_tickets')) {
            SupportTicket::query()->withoutGlobalScopes()
                ->whereNotNull('sla_due_at')
                ->where('sla_due_at', '<', now())
                ->where('sla_breached', false)
                ->whereNotIn('status', ['resolved', 'closed'])
                ->update(['sla_breached' => true, 'escalation_level' => 1]);
        }
    }
}
