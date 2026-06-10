<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface StaffMemberRepositoryInterface extends RepositoryInterface
{
    /** Returns counts of staff grouped by status. */
    public function statusCounts(): array;
}
