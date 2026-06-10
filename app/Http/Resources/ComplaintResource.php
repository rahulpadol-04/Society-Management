<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'reference'    => $this->reference,
            'title'        => $this->title,
            'description'  => $this->description,
            'priority'     => $this->priority,
            'status'       => $this->status,
            'category'     => $this->whenLoaded('category', fn () => $this->category?->name),
            'raised_by'    => $this->whenLoaded('raisedBy', fn () => $this->raisedBy?->only(['id', 'name'])),
            'assigned_to'  => $this->whenLoaded('assignee', fn () => $this->assignee?->only(['id', 'name'])),
            'attachments'  => $this->attachments,
            'sla_due_at'   => $this->sla_due_at,
            'sla_breached' => $this->sla_breached,
            'resolved_at'  => $this->resolved_at,
            'created_at'   => $this->created_at,
        ];
    }
}
