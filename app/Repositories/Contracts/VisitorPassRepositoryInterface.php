<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\VisitorPass;

interface VisitorPassRepositoryInterface extends RepositoryInterface
{
    /** Find a pass by its QR code, within the current tenant scope. */
    public function findByCode(string $code): ?VisitorPass;

    /** Counts by status for the KPI cards on the dashboard. */
    public function statusCounts(): array;
}
