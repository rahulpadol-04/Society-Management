<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'ticket_number'    => $this->ticket_number,
            'subject'          => $this->subject,
            'description'      => $this->description,
            'category'         => $this->category,
            'priority'         => $this->priority,
            'status'           => $this->status,
            'raised_by'        => $this->whenLoaded('raisedBy', fn () => $this->raisedBy?->only(['id', 'name'])),
            'assigned_to'      => $this->whenLoaded('assignee', fn () => $this->assignee?->only(['id', 'name'])),
            'sla_due_at'       => $this->sla_due_at,
            'sla_breached'     => $this->sla_breached,
            'escalation_level' => $this->escalation_level,
            'resolved_at'      => $this->resolved_at,
            'closed_at'        => $this->closed_at,
            'created_at'       => $this->created_at,
        ];
    }
}
