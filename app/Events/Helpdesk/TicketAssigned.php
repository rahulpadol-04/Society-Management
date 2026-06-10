<?php

declare(strict_types=1);

namespace App\Events\Helpdesk;

use App\Models\SupportTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public SupportTicket $ticket) {}
}
