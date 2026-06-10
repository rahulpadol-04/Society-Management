<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Vendors;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendors\StoreVendorRequest;
use App\Http\Requests\Vendors\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Services\Vendors\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    use ApiResponse;

    public function __construct(protected VendorService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Vendor::class);

        $vendors = $this->service->paginate(
            $request->only(['category', 'status', 'search', 'sort', 'dir', 'per_page']),
            [],
        );

        return $this->paginated(
            $vendors->setCollection($vendors->getCollection()->map(fn ($v) => (new VendorResource($v))->resolve()))
        );
    }

    public function store(StoreVendorRequest $request): JsonResponse
    {
        $vendor = $this->service->create($request->validated());

        return $this->created(new VendorResource($vendor), 'Vendor created.');
    }

    public function show(Vendor $vendor): JsonResponse
    {
        $this->authorize('view', $vendor);

        return $this->ok(new VendorResource($vendor->load(['contracts', 'workOrders', 'payments', 'ratings'])));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        $vendor->update($request->validated());

        return $this->ok(new VendorResource($vendor->refresh()), 'Vendor updated.');
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $this->authorize('delete', $vendor);

        $vendor->delete();

        return $this->ok(null, 'Vendor deleted.');
    }
}
