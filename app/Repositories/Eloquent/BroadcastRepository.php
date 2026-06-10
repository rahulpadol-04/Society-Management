<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Broadcast;
use App\Repositories\Contracts\BroadcastRepositoryInterface;

class BroadcastRepository extends BaseRepository implements BroadcastRepositoryInterface
{
    protected array $filterable = ['status', 'audience'];

    protected array $searchable = ['title', 'message'];

    protected function model(): string
    {
        return Broadcast::class;
    }

    public function statusCounts(): array
    {
        return $this->query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }
}
