<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Structure;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Structure\StoreFlatRequest;
use App\Http\Resources\FlatResource;
use App\Models\Flat;
use App\Repositories\Contracts\FlatRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlatController extends Controller
{
    use ApiResponse;

    public function __construct(protected FlatRepositoryInterface $flats) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Flat::class);

        $flats = $this->flats->paginate(
            (int) $request->input('per_page', 15),
            $request->only(['tower_id', 'status', 'type', 'ownership', 'search', 'sort', 'dir']),
            ['tower', 'owner'],
        );

        return $this->paginated(
            $flats->setCollection($flats->getCollection()->map(fn ($f) => (new FlatResource($f))->resolve()))
        );
    }

    public function store(StoreFlatRequest $request): JsonResponse
    {
        $flat = Flat::create($request->validated());

        return $this->created(new FlatResource($flat), 'Unit created.');
    }

    public function show(Flat $flat): JsonResponse
    {
        $this->authorize('view', $flat);

        return $this->ok(new FlatResource($flat->load(['tower', 'owner', 'parkingSlots'])));
    }

    public function update(StoreFlatRequest $request, Flat $flat): JsonResponse
    {
        $flat->update($request->validated());

        return $this->ok(new FlatResource($flat->refresh()), 'Unit updated.');
    }

    public function destroy(Flat $flat): JsonResponse
    {
        $this->authorize('delete', $flat);

        $flat->delete();

        return $this->ok(null, 'Unit deleted.');
    }
}
