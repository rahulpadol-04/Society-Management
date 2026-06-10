<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface FlatRepositoryInterface extends RepositoryInterface
{
    public function statusCounts(): array;

    public function occupancyRate(): float;
}
