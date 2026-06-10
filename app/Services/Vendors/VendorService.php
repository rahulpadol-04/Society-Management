<?php

declare(strict_types=1);

namespace App\Services\Vendors;

use App\Models\Vendor;
use App\Models\VendorPayment;
use App\Models\VendorRating;
use App\Models\WorkOrder;
use App\Repositories\Contracts\VendorRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Encapsulates the vendor lifecycle: work order creation, payment recording
 * and rating computation. All mutations run inside DB transactions.
 */
class VendorService extends BaseService
{
    public function __construct(VendorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function statusCounts(): array
    {
        return $this->repository->statusCounts();
    }

    /**
     * Create a work order for a vendor and generate a human-readable reference
     * in the format WO-YYMM-XXXXX.
     */
    public function createWorkOrder(Vendor $vendor, array $data): WorkOrder
    {
        return DB::transaction(function () use ($vendor, $data) {
            return WorkOrder::create([
                ...$data,
                'vendor_id'  => $vendor->id,
                'reference'  => $this->generateWorkOrderReference(),
                'created_by' => $data['created_by'] ?? auth()->id(),
                'status'     => $data['status'] ?? 'open',
            ]);
        });
    }

    /**
     * Record a payment against a vendor, optionally linking to a work order.
     */
    public function recordPayment(Vendor $vendor, array $data): VendorPayment
    {
        return DB::transaction(function () use ($vendor, $data) {
            return VendorPayment::create([
                ...$data,
                'vendor_id'   => $vendor->id,
                'recorded_by' => $data['recorded_by'] ?? auth()->id(),
                'paid_at'     => $data['paid_at'] ?? now(),
            ]);
        });
    }

    /**
     * Add a rating for a vendor then recompute the rolling average + count.
     */
    public function addRating(Vendor $vendor, int $rating, ?string $comment, int $userId): VendorRating
    {
        return DB::transaction(function () use ($vendor, $rating, $comment, $userId) {
            $vendorRating = VendorRating::create([
                'society_id' => $vendor->society_id,
                'vendor_id'  => $vendor->id,
                'user_id'    => $userId,
                'rating'     => $rating,
                'comment'    => $comment,
            ]);

            $vendor->recalcRating();

            return $vendorRating;
        });
    }

    protected function generateWorkOrderReference(): string
    {
        do {
            $ref = 'WO-'.now()->format('ym').'-'.Str::upper(Str::random(5));
        } while (WorkOrder::withTrashed()->where('reference', $ref)->exists());

        return $ref;
    }
}
