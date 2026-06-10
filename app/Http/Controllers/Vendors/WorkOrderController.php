<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vendors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendors\StoreWorkOrderRequest;
use App\Models\Vendor;
use App\Models\WorkOrder;
use App\Services\Vendors\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function __construct(protected VendorService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WorkOrder::class);

        $workOrders = WorkOrder::with(['vendor'])
            ->latest()
            ->limit(1000)
            ->get();

        $vendors = Vendor::active()->orderBy('name')->get();

        return view('vendors.work-orders.index', compact('workOrders', 'vendors'));
    }

    public function store(StoreWorkOrderRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('create', WorkOrder::class);

        $this->service->createWorkOrder($vendor, $request->validated());

        return back()->with('success', 'Work order created.');
    }

    public function show(WorkOrder $workOrder): View
    {
        $this->authorize('view', $workOrder);

        $workOrder->load(['vendor', 'creator']);

        return view('vendors.work-orders.show', compact('workOrder'));
    }

    public function updateStatus(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('update', $workOrder);

        $data = $request->validate([
            'status' => ['required', 'in:open,assigned,in_progress,completed,cancelled'],
        ]);

        $workOrder->update([
            'status'       => $data['status'],
            'completed_at' => $data['status'] === 'completed' ? now() : $workOrder->completed_at,
        ]);

        return back()->with('success', 'Status updated.');
    }
}
