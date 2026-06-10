<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Communication;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreBroadcastRequest;
use App\Http\Resources\BroadcastResource;
use App\Models\Broadcast;
use App\Services\Communication\CommunicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    use ApiResponse;

    public function __construct(protected CommunicationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Broadcast::class);

        $broadcasts = $this->service->paginate(
            $request->only(['status', 'audience', 'search', 'sort', 'dir', 'per_page']),
            ['creator'],
        );

        return $this->paginated(
            $broadcasts->setCollection(
                $broadcasts->getCollection()->map(fn ($b) => (new BroadcastResource($b))->resolve())
            )
        );
    }

    public function store(StoreBroadcastRequest $request): JsonResponse
    {
        $data               = $request->validated();
        $data['created_by'] = auth()->id();
        $data['status']     = 'draft';

        $broadcast = $this->service->create($data);

        return $this->created(new BroadcastResource($broadcast), 'Broadcast created.');
    }

    public function send(Request $request, Broadcast $broadcast): JsonResponse
    {
        $this->authorize('send', $broadcast);

        if (! in_array($broadcast->status, ['draft', 'failed'], true)) {
            return $this->fail('Broadcast has already been dispatched.', 422);
        }

        $broadcast = $this->service->sendBroadcast($broadcast);

        return $this->ok(new BroadcastResource($broadcast), 'Broadcast queued for delivery.');
    }
}
