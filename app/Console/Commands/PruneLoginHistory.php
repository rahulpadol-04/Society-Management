<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LoginHistory;
use Illuminate\Console\Command;

class PruneLoginHistory extends Command
{
    protected $signature = 'communityos:prune-login-history {--days=90 : Delete login history older than this many days}';

    protected $description = 'Prune old login history records to keep the security log lean';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = LoginHistory::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Pruned {$deleted} login history records older than {$days} days.");

        return self::SUCCESS;
    }
}
