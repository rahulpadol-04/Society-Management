<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Complaints;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaints\StoreComplaintRequest;
use App\Http\Requests\Complaints\UpdateComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Services\Complaints\ComplaintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Resident-facing complaint endpoints. Assignment and status transitions are
 * pushed through the service (not a plain ->update()) because each of those
 * needs to log an activity entry and fire the notification events.
 */
class ComplaintController extends Controller
{
    use ApiResponse;

    public function __construct(protected ComplaintService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Complaint::class);

        $complaints = $this->service->paginate(
            $request->only(['status', 'priority', 'complaint_category_id', 'assigned_to', 'search', 'sort', 'dir', 'per_page']),
            ['category', 'raisedBy', 'assignee'],
        );

        return $this->paginated(
            $complaints->setCollection($complaints->getCollection()->map(fn ($c) => (new ComplaintResource($c))->resolve()))
        );
    }

    public function store(StoreComplaintRequest $request): JsonResponse
    {
        $complaint = $this->service->create($request->validated());

        return $this->created(new ComplaintResource($complaint), 'Complaint registered.');
    }

    public function show(Complaint $complaint): JsonResponse
    {
        $this->authorize('view', $complaint);

        return $this->ok(new ComplaintResource($complaint->load(['category', 'raisedBy', 'assignee', 'activities', 'feedback'])));
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint): JsonResponse
    {
        $data = $request->validated();

        // Route assignment and status changes through the service first so the
        // activity log + events fire, then patch the plain editable fields.
        if (! empty($data['assigned_to'])) {
            $this->service->assign($complaint, (int) $data['assigned_to'], $data['note'] ?? null);
        }
        if (! empty($data['status']) && $data['status'] !== $complaint->status) {
            $this->service->changeStatus($complaint, $data['status'], $data['note'] ?? null);
        }
        $complaint->update(collect($data)->only(['title', 'description', 'complaint_category_id', 'priority'])->all());

        return $this->ok(new ComplaintResource($complaint->refresh()), 'Complaint updated.');
    }

    public function destroy(Complaint $complaint): JsonResponse
    {
        $this->authorize('delete', $complaint);

        $complaint->delete();

        return $this->ok(null, 'Complaint deleted.');
    }
}
