<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface SupportTicketRepositoryInterface extends RepositoryInterface
{
    public function statusCounts(): array;
}
