<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\WorkOrder;
use App\Repositories\Contracts\WorkOrderRepositoryInterface;

class WorkOrderRepository extends BaseRepository implements WorkOrderRepositoryInterface
{
    protected array $filterable = ['status', 'vendor_id', 'priority'];

    protected array $searchable = ['reference', 'title'];

    protected function model(): string
    {
        return WorkOrder::class;
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
