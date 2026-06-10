<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Poll;
use App\Repositories\Contracts\PollRepositoryInterface;

class PollRepository extends BaseRepository implements PollRepositoryInterface
{
    protected array $filterable = ['is_active'];

    protected function model(): string
    {
        return Poll::class;
    }
}
