<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'type'          => $this->type,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'relation'      => $this->relation,
            'is_primary'    => $this->is_primary,
            'photo'         => $this->photo,
            'status'        => $this->status,
            'move_in_date'  => $this->move_in_date?->toDateString(),
            'move_out_date' => $this->move_out_date?->toDateString(),
            'flat'          => $this->whenLoaded('flat', fn () => $this->flat?->only(['id', 'number'])),
            'user'          => $this->whenLoaded('user', fn () => $this->user?->only(['id', 'name', 'email'])),
            'family_members' => $this->whenLoaded('familyMembers', fn () => $this->familyMembers->map(
                fn ($m) => $m->only(['id', 'name', 'relation', 'phone', 'status'])
            )),
            'emergency_contacts' => $this->whenLoaded('emergencyContacts', fn () => $this->emergencyContacts->map(
                fn ($c) => $c->only(['id', 'name', 'phone', 'relation', 'is_primary'])
            )),
            'created_at' => $this->created_at,
        ];
    }
}
