<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface VendorRepositoryInterface extends RepositoryInterface
{
    public function statusCounts(): array;
}
