<?php

declare(strict_types=1);

namespace App\Events\Maintenance;

use App\Models\MaintenanceBill;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(public MaintenanceBill $bill) {}
}
