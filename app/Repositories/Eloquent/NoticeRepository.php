<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Notice;
use App\Repositories\Contracts\NoticeRepositoryInterface;

class NoticeRepository extends BaseRepository implements NoticeRepositoryInterface
{
    protected array $filterable = ['category', 'is_published', 'audience'];

    protected array $searchable = ['title', 'body'];

    protected function model(): string
    {
        return Notice::class;
    }
}
