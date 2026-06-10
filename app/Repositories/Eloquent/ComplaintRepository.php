<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Complaint;
use App\Repositories\Contracts\ComplaintRepositoryInterface;

class ComplaintRepository extends BaseRepository implements ComplaintRepositoryInterface
{
    protected array $filterable = ['status', 'priority', 'complaint_category_id', 'assigned_to', 'raised_by'];

    protected array $searchable = ['reference', 'title', 'description'];

    protected function model(): string
    {
        return Complaint::class;
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
