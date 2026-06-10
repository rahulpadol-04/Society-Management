<?php

declare(strict_types=1);

namespace App\Events\Visitors;

use App\Models\VisitorPass;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitorApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public VisitorPass $pass) {}
}
