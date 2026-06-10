<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Vendors;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendors\StoreWorkOrderRequest;
use App\Http\Resources\WorkOrderResource;
use App\Models\Vendor;
use App\Models\WorkOrder;
use App\Services\Vendors\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    use ApiResponse;

    public function __construct(protected VendorService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkOrder::class);

        $workOrders = WorkOrder::with(['vendor'])
            ->latest()
            ->paginate((int) ($request->input('per_page', 15)));

        return $this->paginated(
            $workOrders->setCollection($workOrders->getCollection()->map(fn ($wo) => (new WorkOrderResource($wo))->resolve()))
        );
    }

    public function store(StoreWorkOrderRequest $request, Vendor $vendor): JsonResponse
    {
        $this->authorize('create', WorkOrder::class);

        $workOrder = $this->service->createWorkOrder($vendor, $request->validated());

        return $this->created(new WorkOrderResource($workOrder->load('vendor')), 'Work order created.');
    }
}
