<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'number'        => $this->number,
            'type'          => $this->type,
            'status'        => $this->status,
            'ownership'     => $this->ownership,
            'carpet_area'   => $this->carpet_area,
            'built_up_area' => $this->built_up_area,
            'bedrooms'      => $this->bedrooms,
            'bathrooms'     => $this->bathrooms,
            'tower'         => $this->whenLoaded('tower', fn () => $this->tower?->only(['id', 'name', 'code'])),
            'owner'         => $this->whenLoaded('owner', fn () => $this->owner?->only(['id', 'name'])),
            'created_at'    => $this->created_at,
        ];
    }
}
