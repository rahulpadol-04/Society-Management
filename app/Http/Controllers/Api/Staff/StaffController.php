<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreStaffMemberRequest;
use App\Http\Requests\Staff\UpdateStaffMemberRequest;
use App\Http\Resources\StaffMemberResource;
use App\Models\StaffMember;
use App\Services\Staff\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    use ApiResponse;

    public function __construct(protected StaffService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StaffMember::class);

        $staff = $this->service->paginate(
            $request->only(['department', 'status', 'shift', 'search', 'sort', 'dir', 'per_page']),
        );

        return $this->paginated(
            $staff->setCollection(
                $staff->getCollection()->map(fn ($s) => (new StaffMemberResource($s))->resolve())
            )
        );
    }

    public function show(StaffMember $staffMember): JsonResponse
    {
        $this->authorize('view', $staffMember);

        return $this->ok(new StaffMemberResource($staffMember));
    }

    public function store(StoreStaffMemberRequest $request): JsonResponse
    {
        $staff = $this->service->create($request->validated());

        return $this->created(new StaffMemberResource($staff));
    }

    public function update(UpdateStaffMemberRequest $request, StaffMember $staffMember): JsonResponse
    {
        $this->service->update($staffMember->id, $request->validated());

        return $this->ok(new StaffMemberResource($staffMember->refresh()));
    }

    public function destroy(StaffMember $staffMember): JsonResponse
    {
        $this->authorize('delete', $staffMember);

        $staffMember->delete();

        return $this->ok(null, 'Staff member deleted.');
    }
}
