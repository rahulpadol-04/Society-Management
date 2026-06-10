<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Maintenance;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RecordPaymentRequest;
use App\Http\Resources\MaintenanceBillResource;
use App\Models\MaintenanceBill;
use App\Services\Maintenance\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillController extends Controller
{
    use ApiResponse;

    public function __construct(protected BillingService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MaintenanceBill::class);

        $bills = $this->service->paginate(
            $request->only(['status', 'flat_id', 'user_id', 'period', 'search', 'sort', 'dir', 'per_page']),
            ['flat', 'resident'],
        );

        return $this->paginated(
            $bills->setCollection(
                $bills->getCollection()->map(fn ($b) => (new MaintenanceBillResource($b))->resolve())
            )
        );
    }

    public function show(MaintenanceBill $bill): JsonResponse
    {
        $this->authorize('view', $bill);

        return $this->ok(
            new MaintenanceBillResource($bill->load(['flat', 'resident', 'payments', 'lateFees']))
        );
    }

    public function pay(RecordPaymentRequest $request, MaintenanceBill $bill): JsonResponse
    {
        $this->authorize('collect', $bill);

        $data = $request->validated();

        $payment = $this->service->recordPayment(
            $bill,
            (float) $data['amount'],
            $data['method'],
            $data['reference'] ?? null,
            $data['paid_at'] ?? null,
            $data['notes'] ?? null,
        );

        return $this->created(
            new MaintenanceBillResource($bill->refresh()->load(['flat', 'resident', 'payments'])),
            'Payment recorded. Receipt: '.$payment->receipt_number
        );
    }
}
