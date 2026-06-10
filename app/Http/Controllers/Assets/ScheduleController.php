<?php

declare(strict_types=1);

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\CompleteMaintenanceRequest;
use App\Http\Requests\Assets\StoreMaintenanceScheduleRequest;
use App\Models\Asset;
use App\Models\AssetMaintenanceSchedule;
use App\Services\Assets\AssetService;
use Illuminate\Http\RedirectResponse;

class ScheduleController extends Controller
{
    public function __construct(protected AssetService $service) {}

    public function store(StoreMaintenanceScheduleRequest $request, Asset $asset): RedirectResponse
    {
        $this->authorize('schedule', $asset);

        $this->service->scheduleMaintenance($asset, $request->validated());

        return redirect()->route('assets.show', $asset)
            ->with('success', 'Maintenance schedule added.');
    }

    public function complete(CompleteMaintenanceRequest $request, AssetMaintenanceSchedule $schedule): RedirectResponse
    {
        $this->authorize('update', $schedule);

        $this->service->completeMaintenance($schedule, $request->validated());

        return redirect()->route('assets.show', $schedule->asset_id)
            ->with('success', 'Maintenance logged and schedule updated.');
    }
}
