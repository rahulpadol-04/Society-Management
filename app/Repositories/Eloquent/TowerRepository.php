<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Tower;
use App\Repositories\Contracts\TowerRepositoryInterface;

class TowerRepository extends BaseRepository implements TowerRepositoryInterface
{
    protected array $filterable = ['status', 'type'];

    protected array $searchable = ['name', 'code'];

    protected function model(): string
    {
        return Tower::class;
    }
}
