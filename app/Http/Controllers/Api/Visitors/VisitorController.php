<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Visitors;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Visitors\CheckInRequest;
use App\Http\Requests\Visitors\StoreVisitorPassRequest;
use App\Http\Resources\VisitorLogResource;
use App\Http\Resources\VisitorPassResource;
use App\Models\VisitorLog;
use App\Models\VisitorPass;
use App\Services\Visitors\VisitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    use ApiResponse;

    public function __construct(protected VisitorService $service) {}

    /** GET /api/v1/passes — list passes (paginated). */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitorPass::class);

        $passes = $this->service->paginate(
            $request->only(['status', 'type', 'host_id', 'search', 'sort', 'dir', 'per_page']),
            ['host', 'flat'],
        );

        return $this->paginated(
            $passes->setCollection(
                $passes->getCollection()->map(fn ($p) => (new VisitorPassResource($p))->resolve())
            )
        );
    }

    /** POST /api/v1/passes — create a visitor pass. */
    public function store(StoreVisitorPassRequest $request): JsonResponse
    {
        $pass = $this->service->createPass($request->validated());

        return $this->created(new VisitorPassResource($pass), 'Visitor pass created.');
    }

    /** POST /api/v1/passes/{pass}/approve */
    public function approve(VisitorPass $pass): JsonResponse
    {
        $this->authorize('approve', $pass);

        $pass = $this->service->approve($pass, auth()->id());

        return $this->ok(new VisitorPassResource($pass), 'Pass approved.');
    }

    /** POST /api/v1/gate/validate — validate a QR code. */
    public function validateCode(Request $request): JsonResponse
    {
        $this->authorize('checkin', VisitorLog::class);

        $data = $request->validate(['code' => ['required', 'string', 'max:30']]);

        $pass = $this->service->validateCode($data['code']);

        if (! $pass) {
            return $this->fail('Invalid or expired pass code.', 422);
        }

        return $this->ok(new VisitorPassResource($pass->load(['host', 'flat'])), 'Pass is valid.');
    }

    /** POST /api/v1/gate/checkin */
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $log = $this->service->checkIn($request->validated());

        return $this->created(new VisitorLogResource($log->load(['pass', 'flat', 'guardUser'])), 'Checked in.');
    }

    /** POST /api/v1/gate/checkout/{log} */
    public function checkOut(VisitorLog $log): JsonResponse
    {
        $this->authorize('checkout', $log);

        $log = $this->service->checkOut($log);

        return $this->ok(new VisitorLogResource($log), 'Checked out.');
    }
}
