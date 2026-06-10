<?php

declare(strict_types=1);

namespace App\Events\Maintenance;

use App\Models\MaintenanceBill;
use App\Models\MaintenancePayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public MaintenanceBill    $bill,
        public MaintenancePayment $payment,
    ) {}
}
