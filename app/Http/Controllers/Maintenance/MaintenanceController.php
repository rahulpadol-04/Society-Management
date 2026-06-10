<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\GenerateBillsRequest;
use App\Http\Requests\Maintenance\RecordPaymentRequest;
use App\Models\Flat;
use App\Models\MaintenanceBill;
use App\Models\MaintenancePayment;
use App\Services\Maintenance\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin/accountant Blade screens for maintenance billing – the bulk
 * bill-generation run, payment collection and CSV exports. Residents never
 * reach here; they go through the API BillController instead.
 */
class MaintenanceController extends Controller
{
    public function __construct(protected BillingService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MaintenanceBill::class);

        $bills = $this->service->repository()->query()
            ->with(['flat', 'resident'])
            ->latest()
            ->limit(1000)
            ->get();

        return view('maintenance.index', [
            'bills'  => $bills,
            'totals' => $this->service->statusTotals(),
        ]);
    }

    public function show(MaintenanceBill $bill): View
    {
        $this->authorize('view', $bill);

        $bill->load(['flat', 'resident', 'payments.recorder', 'lateFees']);

        return view('maintenance.bills.show', [
            'bill' => $bill,
        ]);
    }

    public function generateForm(): View
    {
        $this->authorize('generate', MaintenanceBill::class);

        $towers = \App\Models\Tower::with('flats')->get();

        return view('maintenance.generate', [
            'towers'        => $towers,
            'defaultPeriod' => now()->format('Y-m'),
        ]);
    }

    public function generate(GenerateBillsRequest $request): RedirectResponse
    {
        $data    = $request->validated();
        $flatIds = ! empty($data['flat_ids']) ? $data['flat_ids'] : null;

        $result = $this->service->generateBillsForPeriod($data['period'], $flatIds);

        return redirect()->route('maintenance.index')
            ->with('success', "Generated {$result['count']} bill(s) for period {$data['period']}.");
    }

    public function recordPayment(RecordPaymentRequest $request, MaintenanceBill $bill): RedirectResponse
    {
        $this->authorize('collect', $bill);

        $data = $request->validated();

        $this->service->recordPayment(
            $bill,
            (float) $data['amount'],
            $data['method'],
            $data['reference'] ?? null,
            $data['paid_at'] ?? null,
            $data['notes'] ?? null,
        );

        return redirect()->route('maintenance.bills.show', $bill)
            ->with('success', 'Payment recorded successfully.');
    }

    public function waive(Request $request, MaintenanceBill $bill): RedirectResponse
    {
        $this->authorize('waive', $bill);

        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $this->service->waive($bill, $request->input('reason'));

        return redirect()->route('maintenance.bills.show', $bill)
            ->with('success', 'Bill waived successfully.');
    }

    public function invoice(MaintenanceBill $bill): View
    {
        $this->authorize('view', $bill);

        $bill->load(['flat', 'resident', 'lateFees']);

        return view('maintenance.bills.invoice', [
            'bill'     => $bill,
            'society'  => app('tenancy')->current(),
            'template' => \App\Models\InvoiceTemplate::where('is_default', true)->first(),
        ]);
    }

    public function receipt(MaintenanceBill $bill, MaintenancePayment $payment): View
    {
        $this->authorize('view', $bill);

        $bill->load(['flat', 'resident']);
        $payment->load('recorder');

        return view('maintenance.bills.receipt', [
            'bill'    => $bill,
            'payment' => $payment,
            'society' => app('tenancy')->current(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', MaintenanceBill::class);

        $bills = $this->service->repository()->query()
            ->with(['flat', 'resident'])
            ->when($request->input('period'), fn ($q, $p) => $q->where('period', $p))
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="maintenance-dues-'.now()->format('Ymd').'.csv"',
        ];

        return response()->streamDownload(function () use ($bills): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Bill Number', 'Period', 'Flat', 'Resident', 'Total', 'Paid', 'Balance', 'Status', 'Due Date']);

            foreach ($bills as $bill) {
                fputcsv($out, [
                    $bill->bill_number,
                    $bill->period,
                    $bill->flat?->number ?? 'N/A',
                    $bill->resident?->name ?? 'N/A',
                    $bill->total,
                    $bill->paid_amount,
                    $bill->balance,
                    $bill->status,
                    $bill->due_date?->format('Y-m-d'),
                ]);
            }

            fclose($out);
        }, 'maintenance-dues-'.now()->format('Ymd').'.csv', $headers);
    }
}
