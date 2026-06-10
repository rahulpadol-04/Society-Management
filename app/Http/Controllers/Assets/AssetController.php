<?php

declare(strict_types=1);

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\StoreAssetRequest;
use App\Http\Requests\Assets\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Tower;
use App\Services\Assets\AssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function __construct(protected AssetService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Asset::class);

        $assets = $this->service->repository()->query()
            ->with(['category', 'tower'])
            ->latest()
            ->limit(1000)
            ->get();

        $statusCounts = $this->service->statusCounts();

        $kpi = [
            'total'            => $assets->count(),
            'purchase_value'   => $assets->sum('purchase_cost'),
            'current_value'    => $assets->sum('current_value'),
            'under_maintenance' => $statusCounts['under_maintenance'] ?? 0,
        ];

        return view('assets.index', [
            'assets'     => $assets,
            'kpi'        => $kpi,
            'categories' => AssetCategory::active()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Asset::class);

        return view('assets.create', [
            'categories' => AssetCategory::active()->get(),
            'towers'     => Tower::all(),
        ]);
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('assets/'.current_society_id(), 'public');
        }

        $asset = $this->service->create($data);

        return redirect()->route('assets.show', $asset)
            ->with('success', "Asset \"{$asset->name}\" created.");
    }

    public function show(Asset $asset): View
    {
        $this->authorize('view', $asset);

        $asset->load(['category', 'tower', 'schedules.assignee', 'logs']);

        // Build year-by-year depreciation table.
        $depreciationTable = $this->buildDepreciationTable($asset);

        return view('assets.show', [
            'asset'             => $asset,
            'depreciationTable' => $depreciationTable,
        ]);
    }

    public function edit(Asset $asset): View
    {
        $this->authorize('update', $asset);

        return view('assets.edit', [
            'asset'      => $asset,
            'categories' => AssetCategory::active()->get(),
            'towers'     => Tower::all(),
        ]);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('assets/'.current_society_id(), 'public');
        }

        $this->service->update($asset->id, $data);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset updated.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorize('delete', $asset);

        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Asset deleted.');
    }

    public function depreciate(Asset $asset): RedirectResponse
    {
        $this->authorize('update', $asset);

        $this->service->recomputeDepreciation($asset);

        return back()->with('success', 'Depreciation recomputed.');
    }

    protected function buildDepreciationTable(Asset $asset): array
    {
        if (! $asset->purchase_date || $asset->depreciation_method === 'none') {
            return [];
        }

        $life = (int) ($asset->useful_life_years
            ?? $asset->category?->useful_life_years
            ?? 0);

        if ($life <= 0) {
            return [];
        }

        $rows       = [];
        $purchaseDate = $asset->purchase_date;

        for ($yr = 0; $yr <= $life; $yr++) {
            $asOf  = (clone $purchaseDate)->addYears($yr);
            $value = $asset->depreciatedValue($asOf);

            $rows[] = [
                'year'          => $purchaseDate->year + $yr,
                'age_years'     => $yr,
                'current_value' => $value,
            ];
        }

        return $rows;
    }
}
