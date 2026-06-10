<?php

declare(strict_types=1);

namespace App\Events\Visitors;

use App\Models\VisitorLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitorCheckedIn
{
    use Dispatchable, SerializesModels;

    public function __construct(public VisitorLog $log) {}
}
