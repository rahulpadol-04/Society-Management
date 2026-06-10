<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface LedgerAccountRepositoryInterface extends RepositoryInterface
{
    public function typeSummary(): array;
}
