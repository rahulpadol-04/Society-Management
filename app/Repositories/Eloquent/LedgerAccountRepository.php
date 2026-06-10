<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\LedgerAccount;
use App\Repositories\Contracts\LedgerAccountRepositoryInterface;

class LedgerAccountRepository extends BaseRepository implements LedgerAccountRepositoryInterface
{
    protected array $filterable = ['type', 'subtype', 'is_active'];

    protected array $searchable = ['name', 'code', 'description'];

    protected function model(): string
    {
        return LedgerAccount::class;
    }

    public function typeSummary(): array
    {
        return $this->query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();
    }
}
