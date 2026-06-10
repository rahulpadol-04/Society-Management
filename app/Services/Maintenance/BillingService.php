<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Events\Maintenance\BillGenerated;
use App\Events\Maintenance\PaymentReceived;
use App\Models\Flat;
use App\Models\LateFee;
use App\Models\MaintenanceBill;
use App\Models\MaintenanceHead;
use App\Models\MaintenancePayment;
use App\Repositories\Contracts\MaintenanceBillRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

/**
 * Encapsulates the maintenance billing lifecycle: bill generation, payment
 * recording, late fee application, waiving and overdue marking. All mutations
 * are wrapped in DB transactions. Side effects fire domain events handled by
 * the queued NotifyBillingParties listener.
 */
class BillingService extends BaseService
{
    /** Narrowly-typed reference so static analysis resolves module-specific methods. */
    protected MaintenanceBillRepositoryInterface $billRepository;

    public function __construct(MaintenanceBillRepositoryInterface $repository)
    {
        $this->billRepository = $repository;
        $this->repository     = $repository;
    }

    public function statusTotals(): array
    {
        return $this->billRepository->statusTotals();
    }

    /**
     * Generate maintenance bills for all active flats (or a subset) for the
     * given period. Skips flats that already have a bill for that period.
     *
     * @param  string        $period   'YYYY-MM'
     * @param  int[]|null    $flatIds  Restrict to these flat IDs (null = all)
     * @return array{count: int, bills: \Illuminate\Support\Collection}
     */
    public function generateBillsForPeriod(string $period, ?array $flatIds = null): array
    {
        return DB::transaction(function () use ($period, $flatIds) {
            $heads = MaintenanceHead::active()->get();

            $flatQuery = Flat::query();

            if ($flatIds !== null) {
                $flatQuery->whereIn('id', $flatIds);
            }

            $flats = $flatQuery->get();

            // IDs that already have a bill for this period
            $existingFlatIds = MaintenanceBill::withTrashed()
                ->where('period', $period)
                ->pluck('flat_id')
                ->filter()
                ->all();

            $bills = collect();

            foreach ($flats as $flat) {
                if (in_array($flat->id, $existingFlatIds, true)) {
                    continue;
                }

                $lineItems  = [];
                $subtotal   = 0.0;
                $taxAmount  = 0.0;

                foreach ($heads as $head) {
                    $lineAmount = $this->computeHeadAmount($head, $flat);
                    $lineTax    = 0.0;

                    if ($head->is_taxable) {
                        $gstPct  = $head->gst_percentage ?? (float) config('communityos.billing.gst_percentage', 18);
                        $lineTax = round($lineAmount * $gstPct / 100, 2);
                    }

                    $lineItems[] = [
                        'head'    => $head->name,
                        'amount'  => $lineAmount,
                        'tax'     => $lineTax,
                        'type'    => $head->type,
                    ];

                    $subtotal  += $lineAmount;
                    $taxAmount += $lineTax;
                }

                $total = round($subtotal + $taxAmount, 2);

                $bill = MaintenanceBill::create([
                    'bill_number' => $this->generateBillNumber(),
                    'flat_id'     => $flat->id,
                    'user_id'     => $flat->owner_id,
                    'period'      => $period,
                    'bill_date'   => now()->toDateString(),
                    'due_date'    => $this->computeDueDate($period),
                    'subtotal'    => round($subtotal, 2),
                    'tax_amount'  => round($taxAmount, 2),
                    'late_fee'    => 0,
                    'discount'    => 0,
                    'total'       => $total,
                    'paid_amount' => 0,
                    'status'      => 'unpaid',
                    'line_items'  => $lineItems,
                ]);

                BillGenerated::dispatch($bill);

                $bills->push($bill);
            }

            return ['count' => $bills->count(), 'bills' => $bills];
        });
    }

    /**
     * Record a payment against a bill. Updates bill paid_amount and status.
     */
    public function recordPayment(
        MaintenanceBill $bill,
        float           $amount,
        string          $method = 'cash',
        ?string         $reference = null,
        ?string         $paidAt = null,
        ?string         $notes = null,
    ): MaintenancePayment {
        return DB::transaction(function () use ($bill, $amount, $method, $reference, $paidAt, $notes) {
            $payment = MaintenancePayment::create([
                'receipt_number'       => $this->generateReceiptNumber(),
                'maintenance_bill_id'  => $bill->id,
                'amount'               => $amount,
                'method'               => $method,
                'reference'            => $reference,
                'paid_at'              => $paidAt ? now()->parse($paidAt) : now(),
                'recorded_by'          => auth()->id(),
                'notes'                => $notes,
            ]);

            $newPaid = round($bill->paid_amount + $amount, 2);
            $status  = $newPaid >= $bill->total ? 'paid' : 'partial';

            $bill->update([
                'paid_amount' => $newPaid,
                'status'      => $status,
            ]);

            PaymentReceived::dispatch($bill->refresh(), $payment);

            return $payment;
        });
    }

    /**
     * Apply a late fee to an overdue bill (partial or unpaid only).
     */
    public function applyLateFee(MaintenanceBill $bill, ?float $amount = null): LateFee
    {
        return DB::transaction(function () use ($bill, $amount) {
            $latePct = (float) config('communityos.billing.late_fee_percentage', 2);
            $feeAmt  = $amount ?? round($bill->balance * $latePct / 100, 2);

            $lateFee = LateFee::create([
                'maintenance_bill_id' => $bill->id,
                'amount'              => $feeAmt,
                'reason'              => "Late fee at {$latePct}% of outstanding balance",
                'applied_on'          => now()->toDateString(),
            ]);

            $newLateFee = round($bill->late_fee + $feeAmt, 2);
            $newTotal   = round($bill->subtotal + $bill->tax_amount + $newLateFee - $bill->discount, 2);

            $bill->update([
                'late_fee' => $newLateFee,
                'total'    => $newTotal,
            ]);

            return $lateFee;
        });
    }

    /**
     * Waive a bill (set discount = outstanding or mark as cancelled).
     */
    public function waive(MaintenanceBill $bill, ?string $reason = null): MaintenanceBill
    {
        return DB::transaction(function () use ($bill, $reason) {
            $bill->update([
                'discount' => $bill->total - $bill->paid_amount,
                'status'   => 'cancelled',
                'notes'    => $reason ? ($bill->notes ? $bill->notes."\nWaived: ".$reason : 'Waived: '.$reason) : $bill->notes,
            ]);

            return $bill->refresh();
        });
    }

    /**
     * Mark all past-due unpaid/partial bills as overdue.
     */
    public function markOverdue(): int
    {
        return MaintenanceBill::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function computeHeadAmount(MaintenanceHead $head, Flat $flat): float
    {
        return match ($head->type) {
            'per_sqft'   => round((float) $head->amount * (float) ($flat->carpet_area ?? 0), 2),
            'fixed',
            'per_unit',
            'percentage' => (float) $head->amount,
        };
    }

    protected function computeDueDate(string $period): string
    {
        // Due date = last day of the billing month + 10 grace days
        [$year, $month] = explode('-', $period);
        $endOfMonth = \Carbon\Carbon::create((int) $year, (int) $month)->endOfMonth();

        return $endOfMonth->addDays((int) config('communityos.billing.late_fee_grace_days', 10))->toDateString();
    }

    protected function generateBillNumber(): string
    {
        $prefix = config('communityos.billing.invoice_prefix', 'INV');
        $yymm   = now()->format('ym');

        do {
            $number = "{$prefix}-{$yymm}-".str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (MaintenanceBill::withTrashed()->where('bill_number', $number)->exists());

        return $number;
    }

    protected function generateReceiptNumber(): string
    {
        $prefix = config('communityos.billing.receipt_prefix', 'RCPT');
        $yymm   = now()->format('ym');

        do {
            $number = "{$prefix}-{$yymm}-".str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (MaintenancePayment::where('receipt_number', $number)->exists());

        return $number;
    }
}
