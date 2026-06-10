<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Assets;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\StoreAssetRequest;
use App\Http\Requests\Assets\UpdateAssetRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Services\Assets\AssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    use ApiResponse;

    public function __construct(protected AssetService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Asset::class);

        $assets = $this->service->paginate(
            $request->only(['status', 'asset_category_id', 'tower_id', 'search', 'sort', 'dir', 'per_page']),
            ['category', 'tower'],
        );

        return $this->paginated(
            $assets->setCollection($assets->getCollection()->map(fn ($a) => (new AssetResource($a))->resolve()))
        );
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        $asset = $this->service->create($request->validated());

        return $this->created(new AssetResource($asset), 'Asset created.');
    }

    public function show(Asset $asset): JsonResponse
    {
        $this->authorize('view', $asset);

        return $this->ok(new AssetResource($asset->load(['category', 'tower', 'schedules', 'logs'])));
    }

    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        $this->service->update($asset->id, $request->validated());

        return $this->ok(new AssetResource($asset->refresh()), 'Asset updated.');
    }

    public function destroy(Asset $asset): JsonResponse
    {
        $this->authorize('delete', $asset);

        $asset->delete();

        return $this->ok(null, 'Asset deleted.');
    }
}
