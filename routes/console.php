<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled tasks (Cron)
|--------------------------------------------------------------------------
| Driven by a single system cron entry:
|   * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
|
| Module jobs are scheduled defensively (class_exists) so the scheduler keeps
| working as feature modules are added/removed.
*/

Schedule::command('communityos:prune-login-history --days=90')->dailyAt('01:00');
Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command('queue:prune-batches --hours=48')->daily();
Schedule::command('auth:clear-resets')->everyFifteenMinutes();

// Generate monthly maintenance bills for every society on the 1st.
if (class_exists(\App\Jobs\Maintenance\GenerateMonthlyBills::class)) {
    Schedule::job(new \App\Jobs\Maintenance\GenerateMonthlyBills)->monthlyOn(1, '02:00');
}

// Apply late fees to overdue bills nightly.
if (class_exists(\App\Jobs\Maintenance\ApplyLateFees::class)) {
    Schedule::job(new \App\Jobs\Maintenance\ApplyLateFees)->dailyAt('03:00');
}

// Send maintenance due reminders.
if (class_exists(\App\Jobs\Maintenance\SendDueReminders::class)) {
    Schedule::job(new \App\Jobs\Maintenance\SendDueReminders)->dailyAt('09:00');
}

// Escalate SLA-breaching complaints / helpdesk tickets.
if (class_exists(\App\Jobs\Complaints\EscalateBreachedSlas::class)) {
    Schedule::job(new \App\Jobs\Complaints\EscalateBreachedSlas)->hourly();
}
