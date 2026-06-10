<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'facility'     => $this->whenLoaded('facility', fn () => $this->facility?->only(['id', 'name', 'type'])),
            'booker'       => $this->whenLoaded('booker', fn () => $this->booker?->only(['id', 'name'])),
            'flat'         => $this->whenLoaded('flat', fn () => $this->flat?->only(['id', 'number'])),
            'booking_date' => $this->booking_date?->format('Y-m-d'),
            'start_time'   => $this->start_time,
            'end_time'     => $this->end_time,
            'guests'       => $this->guests,
            'amount'       => $this->amount,
            'status'       => $this->status,
            'notes'        => $this->notes,
            'created_at'   => $this->created_at,
        ];
    }
}
