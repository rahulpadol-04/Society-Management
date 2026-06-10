<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Flat;
use App\Repositories\Contracts\FlatRepositoryInterface;

class FlatRepository extends BaseRepository implements FlatRepositoryInterface
{
    protected array $filterable = ['tower_id', 'floor_id', 'status', 'type', 'ownership', 'owner_id'];

    protected array $searchable = ['number', 'type'];

    protected function model(): string
    {
        return Flat::class;
    }

    public function statusCounts(): array
    {
        return $this->query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }

    public function occupancyRate(): float
    {
        $total = $this->query()->count();

        if ($total === 0) {
            return 0.0;
        }

        $occupied = $this->query()->whereIn('status', ['occupied', 'on_rent'])->count();

        return round(($occupied / $total) * 100, 1);
    }
}
