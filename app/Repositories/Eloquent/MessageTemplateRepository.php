<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\MessageTemplate;
use App\Repositories\Contracts\MessageTemplateRepositoryInterface;

class MessageTemplateRepository extends BaseRepository implements MessageTemplateRepositoryInterface
{
    protected array $filterable = ['channel', 'is_active'];

    protected array $searchable = ['name', 'subject'];

    protected function model(): string
    {
        return MessageTemplate::class;
    }
}
