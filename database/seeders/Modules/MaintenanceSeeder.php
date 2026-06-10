<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\Flat;
use App\Models\LateFee;
use App\Models\MaintenanceBill;
use App\Models\MaintenanceHead;
use App\Models\MaintenancePayment;
use App\Models\Society;
use App\Models\User;
use App\Services\Maintenance\BillingService;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        tenancy()->set($society);

        // Create maintenance heads if not present
        if (! MaintenanceHead::where('society_id', $society->id)->exists()) {
            MaintenanceHead::create([
                'name'        => 'Maintenance Charge',
                'code'        => 'MC',
                'type'        => 'fixed',
                'amount'      => 2000.00,
                'is_taxable'  => false,
                'frequency'   => 'monthly',
                'is_active'   => true,
                'description' => 'Monthly maintenance charge for common amenities',
            ]);

            MaintenanceHead::create([
                'name'        => 'Sinking Fund',
                'code'        => 'SF',
                'type'        => 'fixed',
                'amount'      => 500.00,
                'is_taxable'  => true,
                'gst_percentage' => 18.0,
                'frequency'   => 'monthly',
                'is_active'   => true,
                'description' => 'Monthly contribution to the sinking fund',
            ]);

            MaintenanceHead::create([
                'name'        => 'Water Charges',
                'code'        => 'WC',
                'type'        => 'per_unit',
                'amount'      => 300.00,
                'is_taxable'  => false,
                'frequency'   => 'monthly',
                'is_active'   => true,
                'description' => 'Monthly water usage charges',
            ]);

            MaintenanceHead::create([
                'name'        => 'Parking',
                'code'        => 'PK',
                'type'        => 'fixed',
                'amount'      => 200.00,
                'is_taxable'  => false,
                'frequency'   => 'monthly',
                'is_active'   => true,
                'description' => 'Monthly parking slot charges',
            ]);
        }

        $service = app(BillingService::class);

        // Generate bills for the current period if not already done
        $currentPeriod = now()->format('Y-m');
        if (! MaintenanceBill::where('society_id', $society->id)->where('period', $currentPeriod)->exists()) {
            $service->generateBillsForPeriod($currentPeriod);
        }

        // Also generate backdated bills for the last 5 months for chart data
        for ($i = 1; $i <= 5; $i++) {
            $period = now()->subMonths($i)->format('Y-m');
            if (! MaintenanceBill::where('society_id', $society->id)->where('period', $period)->exists()) {
                $service->generateBillsForPeriod($period);
            }
        }

        $accountant = User::withoutGlobalScopes()->where('email', 'accountant@greenvalley.test')->first()
            ?? User::withoutGlobalScopes()->where('society_id', $society->id)->first();

        // Record payments on some bills (paid + partial)
        $unpaidBills = MaintenanceBill::where('society_id', $society->id)
            ->where('status', 'unpaid')
            ->limit(10)
            ->get();

        $idx = 0;
        foreach ($unpaidBills as $bill) {
            if ($idx % 3 === 0) {
                // Full payment
                $service->recordPayment($bill, $bill->total, 'upi', 'TXN'.random_int(100000, 999999));
            } elseif ($idx % 3 === 1) {
                // Partial payment
                $service->recordPayment($bill, round($bill->total * 0.5, 2), 'cash');
            }
            // else leave unpaid
            $idx++;
        }

        // Make some bills overdue for dashboard variety
        MaintenanceBill::where('society_id', $society->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->limit(3)
            ->get()
            ->each(function (MaintenanceBill $bill): void {
                $bill->update([
                    'due_date' => now()->subDays(15)->toDateString(),
                    'status'   => 'overdue',
                ]);
            });

        // Spread created_at across last 6 months for the collection chart
        $allBills = MaintenanceBill::where('society_id', $society->id)->get();
        foreach ($allBills as $bill) {
            // Compute how many months ago the billing period was and place created_at accordingly.
            $periodDate = \Carbon\Carbon::createFromFormat('Y-m', $bill->period);
            $monthsAgo  = max(0, (int) now()->diffInMonths($periodDate));
            $createdAt  = now()->startOfMonth()->subMonths($monthsAgo)->addDays(rand(1, 15));

            MaintenanceBill::withoutGlobalScopes()
                ->where('id', $bill->id)
                ->update(['created_at' => $createdAt]);
        }

        tenancy()->forget();
    }
}
