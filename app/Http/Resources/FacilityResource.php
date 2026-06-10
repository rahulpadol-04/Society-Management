<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'type'              => $this->type,
            'description'       => $this->description,
            'capacity'          => $this->capacity,
            'charge'            => $this->charge,
            'requires_approval' => $this->requires_approval,
            'opening_time'      => $this->opening_time,
            'closing_time'      => $this->closing_time,
            'slot_minutes'      => $this->slot_minutes,
            'is_active'         => $this->is_active,
            'image'             => $this->image,
            'created_at'        => $this->created_at,
        ];
    }
}
