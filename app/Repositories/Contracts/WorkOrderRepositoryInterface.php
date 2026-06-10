<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface WorkOrderRepositoryInterface extends RepositoryInterface
{
    public function statusCounts(): array;
}
