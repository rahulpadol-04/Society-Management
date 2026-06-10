<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\JournalEntry;
use App\Repositories\Contracts\JournalEntryRepositoryInterface;

class JournalEntryRepository extends BaseRepository implements JournalEntryRepositoryInterface
{
    protected array $filterable = ['status', 'type'];

    protected array $searchable = ['reference', 'narration'];

    protected function model(): string
    {
        return JournalEntry::class;
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
