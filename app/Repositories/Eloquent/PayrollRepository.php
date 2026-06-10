<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Payroll;
use App\Repositories\Contracts\PayrollRepositoryInterface;

class PayrollRepository extends BaseRepository implements PayrollRepositoryInterface
{
    protected array $filterable = ['status', 'period', 'staff_member_id'];

    protected function model(): string
    {
        return Payroll::class;
    }
}
