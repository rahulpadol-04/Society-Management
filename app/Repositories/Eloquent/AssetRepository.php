<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Asset;
use App\Repositories\Contracts\AssetRepositoryInterface;

class AssetRepository extends BaseRepository implements AssetRepositoryInterface
{
    protected array $filterable = ['status', 'asset_category_id', 'tower_id'];

    protected array $searchable = ['name', 'code', 'location'];

    protected function model(): string
    {
        return Asset::class;
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
