<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Residents;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Residents\StoreResidentRequest;
use App\Http\Requests\Residents\UpdateResidentRequest;
use App\Http\Resources\ResidentResource;
use App\Models\Resident;
use App\Services\Residents\ResidentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    use ApiResponse;

    public function __construct(protected ResidentService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Resident::class);

        $residents = $this->service->paginate(
            $request->only(['type', 'status', 'flat_id', 'search', 'sort', 'dir', 'per_page']),
            ['flat', 'user'],
        );

        return $this->paginated(
            $residents->setCollection(
                $residents->getCollection()->map(fn ($r) => (new ResidentResource($r))->resolve())
            )
        );
    }

    public function store(StoreResidentRequest $request): JsonResponse
    {
        $resident = $this->service->create($request->validated());

        return $this->created(new ResidentResource($resident), 'Resident registered.');
    }

    public function show(Resident $resident): JsonResponse
    {
        $this->authorize('view', $resident);

        $resident->load(['flat', 'user', 'familyMembers', 'emergencyContacts', 'vehicles']);

        return $this->ok(new ResidentResource($resident));
    }

    public function update(UpdateResidentRequest $request, Resident $resident): JsonResponse
    {
        $this->service->update($resident->id, $request->validated());

        return $this->ok(new ResidentResource($resident->refresh()->load(['flat', 'user'])), 'Resident updated.');
    }

    public function destroy(Resident $resident): JsonResponse
    {
        $this->authorize('delete', $resident);

        $resident->delete();

        return $this->ok(null, 'Resident deleted.');
    }
}
