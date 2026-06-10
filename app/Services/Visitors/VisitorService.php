<?php

declare(strict_types=1);

namespace App\Services\Visitors;

use App\Events\Visitors\VisitorApproved;
use App\Events\Visitors\VisitorCheckedIn;
use App\Events\Visitors\VisitorPassRequested;
use App\Models\VisitorLog;
use App\Models\VisitorPass;
use App\Repositories\Contracts\VisitorPassRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Encapsulates the visitor lifecycle: pass creation, approval, rejection,
 * gate check-in and check-out. Side effects (notifications) are fired via
 * domain events handled by queued listeners.
 */
class VisitorService extends BaseService
{
    public function __construct(VisitorPassRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function statusCounts(): array
    {
        return $this->repository->statusCounts();
    }

    /**
     * Create a new visitor pass. If the acting user is staff (has approve
     * ability) the pass is auto-approved; otherwise it is 'pending'.
     */
    public function createPass(array $data): VisitorPass
    {
        return DB::transaction(function () use ($data) {
            $isStaff = auth()->check() && auth()->user()->can('visitors.approve');

            /** @var VisitorPass $pass */
            $pass = $this->repository->create([
                ...$data,
                'code'        => $this->generateCode(),
                'host_id'     => $data['host_id'] ?? auth()->id(),
                'status'      => $isStaff ? 'approved' : 'pending',
                'approved_by' => $isStaff ? auth()->id() : null,
                'approved_at' => $isStaff ? now() : null,
            ]);

            VisitorPassRequested::dispatch($pass);

            return $pass;
        });
    }

    /** Approve a pending visitor pass. */
    public function approve(VisitorPass $pass, int $userId): VisitorPass
    {
        return DB::transaction(function () use ($pass, $userId) {
            $pass->update([
                'status'      => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            VisitorApproved::dispatch($pass->refresh());

            return $pass;
        });
    }

    /** Reject a visitor pass with an optional reason stored in purpose. */
    public function reject(VisitorPass $pass, ?string $reason = null): VisitorPass
    {
        $pass->update([
            'status'  => 'rejected',
            'purpose' => $reason ?? $pass->purpose,
        ]);

        return $pass->refresh();
    }

    /**
     * Record a gate check-in. If $data contains a 'code' key the matching pass
     * is used to pre-fill visitor details; otherwise a walk-in entry is created.
     * Increments entries_used on the pass and marks it 'used' when maxed.
     */
    public function checkIn(array $data): VisitorLog
    {
        return DB::transaction(function () use ($data) {
            $pass = null;

            if (! empty($data['code'])) {
                $pass = $this->repository->findByCode($data['code']);
            } elseif (! empty($data['visitor_pass_id'])) {
                $pass = $this->repository->find((int) $data['visitor_pass_id']);
            }

            $logData = [
                'visitor_pass_id' => $pass?->id,
                'flat_id'         => $data['flat_id'] ?? $pass?->flat_id,
                'guard_id'        => $data['guard_id'] ?? auth()->id(),
                'name'            => $data['name'] ?? $pass?->name ?? 'Unknown',
                'phone'           => $data['phone'] ?? $pass?->phone,
                'type'            => $data['type'] ?? $pass?->type ?? 'guest',
                'purpose'         => $data['purpose'] ?? $pass?->purpose,
                'vehicle_number'  => $data['vehicle_number'] ?? $pass?->vehicle_number,
                'gate'            => $data['gate'] ?? null,
                'checked_in_at'   => $data['checked_in_at'] ?? now(),
                'status'          => 'in',
            ];

            /** @var VisitorLog $log */
            $log = VisitorLog::create(array_filter($logData, fn ($v) => $v !== null));

            if ($pass) {
                $entriesUsed = $pass->entries_used + 1;
                $newStatus = ($entriesUsed >= $pass->max_entries) ? 'used' : $pass->status;

                $pass->update([
                    'entries_used' => $entriesUsed,
                    'status'       => $newStatus,
                ]);
            }

            VisitorCheckedIn::dispatch($log);

            return $log;
        });
    }

    /** Mark a visitor log entry as checked out. */
    public function checkOut(VisitorLog $log): VisitorLog
    {
        $log->update([
            'checked_out_at' => now(),
            'status'         => 'out',
        ]);

        return $log->refresh();
    }

    /**
     * Validate a QR code and return the usable pass or null.
     * Used by the guard's QR scan endpoint.
     */
    public function validateCode(string $code): ?VisitorPass
    {
        $pass = $this->repository->findByCode($code);

        return ($pass && $pass->isUsable()) ? $pass : null;
    }

    /** Generate a unique QR/pass code in the format VP-YYMM-XXXXX. */
    protected function generateCode(): string
    {
        do {
            $code = 'VP-'.now()->format('ym').'-'.Str::upper(Str::random(5));
        } while (VisitorPass::withTrashed()->where('code', $code)->exists());

        return $code;
    }
}
