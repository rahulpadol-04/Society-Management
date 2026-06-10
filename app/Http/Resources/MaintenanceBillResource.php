<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceBillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'bill_number' => $this->bill_number,
            'period'      => $this->period,
            'bill_date'   => $this->bill_date,
            'due_date'    => $this->due_date,
            'subtotal'    => $this->subtotal,
            'tax_amount'  => $this->tax_amount,
            'late_fee'    => $this->late_fee,
            'discount'    => $this->discount,
            'total'       => $this->total,
            'paid_amount' => $this->paid_amount,
            'balance'     => $this->balance,
            'status'      => $this->status,
            'line_items'  => $this->line_items,
            'notes'       => $this->notes,
            'flat'        => $this->whenLoaded('flat', fn () => $this->flat?->only(['id', 'number'])),
            'resident'    => $this->whenLoaded('resident', fn () => $this->resident?->only(['id', 'name', 'email'])),
            'payments'    => $this->whenLoaded('payments', fn () => $this->payments->map(fn ($p) => [
                'id'             => $p->id,
                'receipt_number' => $p->receipt_number,
                'amount'         => $p->amount,
                'method'         => $p->method,
                'paid_at'        => $p->paid_at,
            ])),
            'created_at'  => $this->created_at,
        ];
    }
}
