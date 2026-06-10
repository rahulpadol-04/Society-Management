<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface MaintenanceBillRepositoryInterface extends RepositoryInterface
{
    /** Returns aggregated totals: total_billed, total_paid, outstanding. */
    public function statusTotals(): array;
}
