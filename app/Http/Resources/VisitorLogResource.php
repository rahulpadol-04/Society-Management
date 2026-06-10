<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'visitor_pass_id' => $this->visitor_pass_id,
            'name'            => $this->name,
            'phone'           => $this->phone,
            'type'            => $this->type,
            'purpose'         => $this->purpose,
            'vehicle_number'  => $this->vehicle_number,
            'gate'            => $this->gate,
            'status'          => $this->status,
            'checked_in_at'   => $this->checked_in_at,
            'checked_out_at'  => $this->checked_out_at,
            'pass'            => $this->whenLoaded('pass', fn () => $this->pass?->only(['id', 'code'])),
            'guard'           => $this->whenLoaded('guardUser', fn () => $this->guardUser?->only(['id', 'name'])),
            'flat'            => $this->whenLoaded('flat', fn () => $this->flat?->only(['id', 'number'])),
            'created_at'      => $this->created_at,
        ];
    }
}
