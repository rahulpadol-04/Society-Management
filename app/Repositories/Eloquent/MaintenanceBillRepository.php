<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\MaintenanceBill;
use App\Repositories\Contracts\MaintenanceBillRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceBillRepository extends BaseRepository implements MaintenanceBillRepositoryInterface
{
    protected array $filterable = ['status', 'flat_id', 'user_id', 'period'];

    protected array $searchable = ['bill_number'];

    protected function model(): string
    {
        return MaintenanceBill::class;
    }

    public function statusTotals(): array
    {
        $row = $this->query()
            ->selectRaw('
                SUM(total)       AS total_billed,
                SUM(paid_amount) AS total_paid,
                SUM(total - paid_amount) AS outstanding
            ')
            ->first();

        $overdue = $this->query()
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->whereDate('due_date', '<', now())
            ->count();

        return [
            'total_billed'  => (float) ($row->total_billed  ?? 0),
            'total_paid'    => (float) ($row->total_paid    ?? 0),
            'outstanding'   => (float) ($row->outstanding   ?? 0),
            'overdue_count' => $overdue,
        ];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        // Allow filtering by status array
        if (! empty($filters['statuses']) && is_array($filters['statuses'])) {
            $query->whereIn('status', $filters['statuses']);
        }
    }
}
