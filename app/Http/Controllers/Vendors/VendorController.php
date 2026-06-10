<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vendors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendors\StoreContractRequest;
use App\Http\Requests\Vendors\StorePaymentRequest;
use App\Http\Requests\Vendors\StoreRatingRequest;
use App\Http\Requests\Vendors\StoreVendorRequest;
use App\Http\Requests\Vendors\UpdateVendorRequest;
use App\Models\Vendor;
use App\Models\VendorContract;
use App\Services\Vendors\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function __construct(protected VendorService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vendor::class);

        $vendors = $this->service->repository()->query()
            ->withCount(['workOrders', 'contracts'])
            ->latest()
            ->limit(1000)
            ->get();

        return view('vendors.index', [
            'vendors'      => $vendors,
            'statusCounts' => $this->service->statusCounts(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Vendor::class);

        return view('vendors.create');
    }

    public function store(StoreVendorRequest $request): RedirectResponse
    {
        $vendor = $this->service->create($request->validated());

        return redirect()->route('vendors.show', $vendor)
            ->with('success', "Vendor {$vendor->name} created.");
    }

    public function show(Vendor $vendor): View
    {
        $this->authorize('view', $vendor);

        $vendor->load([
            'contracts',
            'workOrders.creator',
            'payments.workOrder',
            'ratings',
        ]);

        return view('vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor): View
    {
        $this->authorize('update', $vendor);

        return view('vendors.edit', compact('vendor'));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): RedirectResponse
    {
        $vendor->update($request->validated());

        return redirect()->route('vendors.show', $vendor)->with('success', 'Vendor updated.');
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        $this->authorize('delete', $vendor);

        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted.');
    }

    public function storeContract(StoreContractRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('create', Vendor::class);

        VendorContract::create([
            ...$request->validated(),
            'vendor_id'  => $vendor->id,
            'society_id' => $vendor->society_id,
        ]);

        return back()->with('success', 'Contract added.');
    }

    public function destroyContract(Vendor $vendor, VendorContract $contract): RedirectResponse
    {
        $this->authorize('delete', $vendor);

        $contract->delete();

        return back()->with('success', 'Contract removed.');
    }

    public function storePayment(StorePaymentRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('pay', $vendor);

        $this->service->recordPayment($vendor, $request->validated());

        return back()->with('success', 'Payment recorded.');
    }

    public function storeRating(StoreRatingRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('rate', $vendor);

        $this->service->addRating(
            $vendor,
            (int) $request->validated('rating'),
            $request->validated('comment'),
            auth()->id(),
        );

        return back()->with('success', 'Rating submitted.');
    }
}
