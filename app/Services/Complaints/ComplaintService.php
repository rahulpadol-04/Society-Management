<?php

declare(strict_types=1);

namespace App\Services\Complaints;

use App\Events\Complaints\ComplaintAssigned;
use App\Events\Complaints\ComplaintCreated;
use App\Events\Complaints\ComplaintStatusChanged;
use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\ComplaintCategory;
use App\Repositories\Contracts\ComplaintRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Encapsulates the complaint lifecycle: creation (with SLA computation),
 * assignment, status transitions, timeline logging and feedback. Side effects
 * (notifications) are fired via domain events handled by queued listeners.
 */
class ComplaintService extends BaseService
{
    public function __construct(ComplaintRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function statusCounts(): array
    {
        return $this->repository->statusCounts();
    }

    public function create(array $data): Complaint
    {
        return DB::transaction(function () use ($data) {
            $slaHours = $this->resolveSlaHours($data['complaint_category_id'] ?? null);

            $complaint = $this->repository->create([
                ...$data,
                'reference'  => $this->generateReference(),
                'raised_by'  => $data['raised_by'] ?? auth()->id(),
                'status'     => 'open',
                'sla_due_at' => now()->addHours($slaHours),
            ]);

            $this->log($complaint, 'created', note: 'Complaint registered');

            ComplaintCreated::dispatch($complaint);

            return $complaint;
        });
    }

    public function assign(Complaint $complaint, int $userId, ?string $note = null): Complaint
    {
        return DB::transaction(function () use ($complaint, $userId, $note) {
            $complaint->update([
                'assigned_to' => $userId,
                'assigned_at' => now(),
                'status'      => $complaint->status === 'open' ? 'assigned' : $complaint->status,
            ]);

            $this->log($complaint, 'assigned', note: $note ?? 'Assigned to staff');

            ComplaintAssigned::dispatch($complaint);

            return $complaint->refresh();
        });
    }

    public function changeStatus(Complaint $complaint, string $status, ?string $note = null): Complaint
    {
        return DB::transaction(function () use ($complaint, $status, $note) {
            $from = $complaint->status;

            $complaint->update([
                'status'          => $status,
                'resolution_note' => $status === 'resolved' ? ($note ?? $complaint->resolution_note) : $complaint->resolution_note,
                'resolved_at'     => $status === 'resolved' ? now() : $complaint->resolved_at,
                'closed_at'       => $status === 'closed' ? now() : $complaint->closed_at,
            ]);

            $this->log($complaint, 'status_changed', $from, $status, $note);

            ComplaintStatusChanged::dispatch($complaint, $from, $status);

            return $complaint->refresh();
        });
    }

    public function addFeedback(Complaint $complaint, int $rating, ?string $comment = null): void
    {
        $complaint->feedback()->updateOrCreate(
            ['complaint_id' => $complaint->id],
            ['society_id' => $complaint->society_id, 'user_id' => auth()->id(), 'rating' => $rating, 'comment' => $comment]
        );

        $this->log($complaint, 'feedback', note: "Rated {$rating}/5");
    }

    protected function log(Complaint $complaint, string $action, ?string $from = null, ?string $to = null, ?string $note = null): void
    {
        ComplaintActivity::create([
            'society_id'   => $complaint->society_id,
            'complaint_id' => $complaint->id,
            'user_id'      => auth()->id(),
            'action'       => $action,
            'from_status'  => $from,
            'to_status'    => $to,
            'note'         => $note,
        ]);
    }

    protected function resolveSlaHours(?int $categoryId): int
    {
        $default = (int) config('communityos.complaints.default_sla_hours', 48);

        if (! $categoryId) {
            return $default;
        }

        return (int) (ComplaintCategory::find($categoryId)?->sla_hours ?? $default);
    }

    protected function generateReference(): string
    {
        do {
            $ref = 'CMP-'.now()->format('ym').'-'.Str::upper(Str::random(5));
        } while (Complaint::withTrashed()->where('reference', $ref)->exists());

        return $ref;
    }
}
