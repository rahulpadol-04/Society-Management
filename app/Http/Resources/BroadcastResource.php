<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BroadcastResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'message'          => $this->message,
            'channels'         => $this->channels,
            'audience'         => $this->audience,
            'status'           => $this->status,
            'recipients_count' => $this->recipients_count,
            'scheduled_at'     => $this->scheduled_at,
            'sent_at'          => $this->sent_at,
            'created_by'       => $this->whenLoaded('creator', fn () => $this->creator?->only(['id', 'name'])),
            'created_at'       => $this->created_at,
        ];
    }
}
