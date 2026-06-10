<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'reference'  => $this->reference,
            'entry_date' => $this->entry_date?->toDateString(),
            'narration'  => $this->narration,
            'type'       => $this->type,
            'status'     => $this->status,
            'amount'     => $this->amount,
            'source'     => $this->source,
            'created_by' => $this->whenLoaded('creator', fn () => $this->creator?->only(['id', 'name'])),
            'posted_by'  => $this->whenLoaded('poster', fn () => $this->poster?->only(['id', 'name'])),
            'posted_at'  => $this->posted_at,
            'lines'      => $this->whenLoaded('lines', fn () => $this->lines->map(fn ($line) => [
                'id'                => $line->id,
                'ledger_account_id' => $line->ledger_account_id,
                'account_name'      => $line->account?->name,
                'debit'             => $line->debit,
                'credit'            => $line->credit,
                'memo'              => $line->memo,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}
