<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'reference'      => $this->reference,
            'title'          => $this->title,
            'description'    => $this->description,
            'priority'       => $this->priority,
            'status'         => $this->status,
            'amount'         => $this->amount,
            'scheduled_for'  => $this->scheduled_for,
            'completed_at'   => $this->completed_at,
            'vendor'         => $this->whenLoaded('vendor', fn () => $this->vendor?->only(['id', 'name', 'company'])),
            'creator'        => $this->whenLoaded('creator', fn () => $this->creator?->only(['id', 'name'])),
            'created_at'     => $this->created_at,
        ];
    }
}
