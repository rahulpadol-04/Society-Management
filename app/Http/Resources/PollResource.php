<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'question'        => $this->question,
            'description'     => $this->description,
            'multiple_choice' => $this->multiple_choice,
            'closes_at'       => $this->closes_at,
            'is_active'       => $this->is_active,
            'total_votes'     => $this->totalVotes(),
            'options'         => $this->whenLoaded('options', fn () => $this->options->map(fn ($option) => [
                'id'          => $option->id,
                'label'       => $option->label,
                'votes_count' => $option->votes_count,
            ])),
            'created_at'      => $this->created_at,
        ];
    }
}
