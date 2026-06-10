<?php

declare(strict_types=1);

namespace App\Events\Complaints;

use App\Models\Complaint;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComplaintCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Complaint $complaint) {}
}
