<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\SocietyDocument;
use App\Repositories\Contracts\SocietyDocumentRepositoryInterface;

class SocietyDocumentRepository extends BaseRepository implements SocietyDocumentRepositoryInterface
{
    protected array $filterable = ['category', 'is_public'];

    protected array $searchable = ['title', 'file_name'];

    protected function model(): string
    {
        return SocietyDocument::class;
    }
}
