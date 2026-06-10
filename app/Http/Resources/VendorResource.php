<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'company'        => $this->company,
            'category'       => $this->category,
            'contact_person' => $this->contact_person,
            'phone'          => $this->phone,
            'email'          => $this->email,
            'gstin'          => $this->gstin,
            'address'        => $this->address,
            'rating'         => $this->rating,
            'ratings_count'  => $this->ratings_count,
            'status'         => $this->status,
            'notes'          => $this->notes,
            'contracts'      => $this->whenLoaded('contracts'),
            'work_orders'    => $this->whenLoaded('workOrders'),
            'payments'       => $this->whenLoaded('payments'),
            'ratings'        => $this->whenLoaded('ratings'),
            'created_at'     => $this->created_at,
        ];
    }
}
