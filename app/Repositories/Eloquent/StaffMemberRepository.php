<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\StaffMember;
use App\Repositories\Contracts\StaffMemberRepositoryInterface;

class StaffMemberRepository extends BaseRepository implements StaffMemberRepositoryInterface
{
    protected array $filterable = ['department', 'status', 'shift'];

    protected array $searchable = ['name', 'employee_code', 'phone'];

    protected function model(): string
    {
        return StaffMember::class;
    }

    public function statusCounts(): array
    {
        $counts = $this->query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'total'      => array_sum($counts),
            'active'     => (int) ($counts['active']     ?? 0),
            'inactive'   => (int) ($counts['inactive']   ?? 0),
            'on_leave'   => (int) ($counts['on_leave']   ?? 0),
            'terminated' => (int) ($counts['terminated'] ?? 0),
        ];
    }
}
