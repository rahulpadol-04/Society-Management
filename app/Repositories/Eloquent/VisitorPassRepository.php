<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\VisitorPass;
use App\Repositories\Contracts\VisitorPassRepositoryInterface;

class VisitorPassRepository extends BaseRepository implements VisitorPassRepositoryInterface
{
    protected array $filterable = ['status', 'type', 'host_id', 'flat_id'];

    protected array $searchable = ['code', 'name', 'phone', 'vehicle_number'];

    protected function model(): string
    {
        return VisitorPass::class;
    }

    public function findByCode(string $code): ?VisitorPass
    {
        return $this->query()->where('code', $code)->first();
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
