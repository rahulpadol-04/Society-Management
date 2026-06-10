<?php

declare(strict_types=1);

namespace App\Events\Helpdesk;

use App\Models\SupportTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public SupportTicket $ticket) {}
}
