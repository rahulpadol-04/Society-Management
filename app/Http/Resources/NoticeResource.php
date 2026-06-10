<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoticeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'body'         => $this->body,
            'category'     => $this->category,
            'audience'     => $this->audience,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'pinned'       => $this->pinned,
            'event_at'     => $this->event_at,
            'attachment'   => $this->attachment,
            'author'       => $this->whenLoaded('author', fn () => $this->author?->only(['id', 'name'])),
            'poll'         => $this->whenLoaded('poll', fn () => new PollResource($this->poll)),
            'created_at'   => $this->created_at,
        ];
    }
}
