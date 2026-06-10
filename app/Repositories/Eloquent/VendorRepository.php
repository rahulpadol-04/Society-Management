<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Vendor;
use App\Repositories\Contracts\VendorRepositoryInterface;

class VendorRepository extends BaseRepository implements VendorRepositoryInterface
{
    protected array $filterable = ['category', 'status'];

    protected array $searchable = ['name', 'company', 'phone'];

    protected function model(): string
    {
        return Vendor::class;
    }

    public function statusCounts(): array
    {
        return $this->query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }
}
