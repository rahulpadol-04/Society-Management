<?php

declare(strict_types=1);

namespace App\Events\Facilities;

use App\Models\FacilityBooking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FacilityBooked
{
    use Dispatchable, SerializesModels;

    public function __construct(public FacilityBooking $booking) {}
}
