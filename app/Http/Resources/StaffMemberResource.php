<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'employee_code' => $this->employee_code,
            'designation'   => $this->designation,
            'department'    => $this->department,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'joining_date'  => $this->joining_date?->format('Y-m-d'),
            'salary'        => $this->salary,
            'shift'         => $this->shift,
            'status'        => $this->status,
            'photo'         => $this->photo,
            'address'       => $this->address,
            'created_at'    => $this->created_at,
        ];
    }
}
