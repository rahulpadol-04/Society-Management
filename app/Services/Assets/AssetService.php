<?php

declare(strict_types=1);

namespace App\Services\Assets;

use App\Models\Asset;
use App\Models\AssetMaintenanceLog;
use App\Models\AssetMaintenanceSchedule;
use App\Repositories\Contracts\AssetRepositoryInterface;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates the asset lifecycle: creation (with initial current_value),
 * depreciation recomputation, maintenance scheduling and completion logging.
 */
class AssetService extends BaseService
{
    public function __construct(AssetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function statusCounts(): array
    {
        return $this->repository->statusCounts();
    }

    public function create(array $data): Asset
    {
        return DB::transaction(function () use ($data): Asset {
            // Set initial current_value equal to purchase_cost.
            if (! isset($data['current_value'])) {
                $data['current_value'] = $data['purchase_cost'] ?? 0;
            }

            /** @var Asset $asset */
            $asset = $this->repository->create($data);

            // Immediately recompute so the depreciation formula is applied from day one.
            $this->recomputeDepreciation($asset);

            return $asset->refresh();
        });
    }

    public function update(int|string $id, array $data): Asset
    {
        return DB::transaction(function () use ($id, $data): Asset {
            /** @var Asset $asset */
            $asset = $this->repository->update($id, $data);

            $this->recomputeDepreciation($asset);

            return $asset->refresh();
        });
    }

    public function recomputeDepreciation(Asset $asset): void
    {
        $asset->loadMissing('category');

        $currentValue = $asset->depreciatedValue();

        $asset->updateQuietly(['current_value' => $currentValue]);
    }

    public function scheduleMaintenance(Asset $asset, array $data): AssetMaintenanceSchedule
    {
        return DB::transaction(function () use ($asset, $data): AssetMaintenanceSchedule {
            $data['asset_id']   = $asset->id;
            $data['society_id'] = $asset->society_id;

            return AssetMaintenanceSchedule::create($data);
        });
    }

    public function completeMaintenance(AssetMaintenanceSchedule $schedule, array $data): AssetMaintenanceLog
    {
        return DB::transaction(function () use ($schedule, $data): AssetMaintenanceLog {
            $performedOn = isset($data['performed_on'])
                ? Carbon::parse($data['performed_on'])
                : now();

            $log = AssetMaintenanceLog::create([
                'society_id'                      => $schedule->society_id,
                'asset_id'                        => $schedule->asset_id,
                'asset_maintenance_schedule_id'   => $schedule->id,
                'performed_on'                    => $performedOn->toDateString(),
                'cost'                            => $data['cost'] ?? 0,
                'performed_by'                    => $data['performed_by'] ?? null,
                'vendor_id'                       => $data['vendor_id'] ?? null,
                'notes'                           => $data['notes'] ?? null,
            ]);

            $nextDue = $this->computeNextDue($schedule->frequency, $performedOn);

            $schedule->update([
                'last_done_date' => $performedOn->toDateString(),
                'next_due_date'  => $nextDue?->toDateString(),
                'status'         => $schedule->frequency === 'one_time' ? 'completed' : 'scheduled',
            ]);

            return $log;
        });
    }

    protected function computeNextDue(string $frequency, Carbon $from): ?Carbon
    {
        return match ($frequency) {
            'weekly'       => (clone $from)->addWeek(),
            'monthly'      => (clone $from)->addMonth(),
            'quarterly'    => (clone $from)->addMonths(3),
            'half_yearly'  => (clone $from)->addMonths(6),
            'yearly'       => (clone $from)->addYear(),
            'one_time'     => null,
            default        => (clone $from)->addMonth(),
        };
    }
}
