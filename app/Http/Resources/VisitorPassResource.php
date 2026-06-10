<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorPassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'code'           => $this->code,
            'name'           => $this->name,
            'phone'          => $this->phone,
            'type'           => $this->type,
            'purpose'        => $this->purpose,
            'vehicle_number' => $this->vehicle_number,
            'expected_at'    => $this->expected_at,
            'valid_until'    => $this->valid_until,
            'max_entries'    => $this->max_entries,
            'entries_used'   => $this->entries_used,
            'status'         => $this->status,
            'is_usable'      => $this->isUsable(),
            'host'           => $this->whenLoaded('host', fn () => $this->host?->only(['id', 'name'])),
            'flat'           => $this->whenLoaded('flat', fn () => $this->flat?->only(['id', 'number'])),
            'approved_at'    => $this->approved_at,
            'created_at'     => $this->created_at,
        ];
    }
}
