<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'code'                => $this->code,
            'name'                => $this->name,
            'description'         => $this->description,
            'location'            => $this->location,
            'category'            => $this->whenLoaded('category', fn () => $this->category?->only(['id', 'name'])),
            'tower'               => $this->whenLoaded('tower', fn () => $this->tower?->only(['id', 'name'])),
            'purchase_date'       => $this->purchase_date?->toDateString(),
            'purchase_cost'       => $this->purchase_cost,
            'salvage_value'       => $this->salvage_value,
            'depreciation_method' => $this->depreciation_method,
            'depreciation_rate'   => $this->depreciation_rate,
            'useful_life_years'   => $this->useful_life_years,
            'current_value'       => $this->current_value,
            'status'              => $this->status,
            'warranty_until'      => $this->warranty_until?->toDateString(),
            'image'               => $this->image,
            'created_at'          => $this->created_at,
        ];
    }
}
