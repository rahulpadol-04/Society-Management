<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\SupportTicket;
use App\Repositories\Contracts\SupportTicketRepositoryInterface;

class SupportTicketRepository extends BaseRepository implements SupportTicketRepositoryInterface
{
    protected array $filterable = ['status', 'priority', 'category', 'assigned_to', 'raised_by'];

    protected array $searchable = ['ticket_number', 'subject', 'description'];

    protected function model(): string
    {
        return SupportTicket::class;
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
