<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\Society;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorContract;
use App\Models\VendorPayment;
use App\Models\VendorRating;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        tenancy()->set($society);

        $admin = User::withoutGlobalScopes()->where('email', 'admin@greenvalley.test')->first();

        if (Vendor::where('society_id', $society->id)->exists()) {
            tenancy()->forget();
            return;
        }

        // --- Vendors ---
        $vendors = [
            ['name' => 'AquaFix Solutions',       'category' => 'plumbing',      'contact_person' => 'Ramesh Patel',   'phone' => '9800001111', 'status' => 'active'],
            ['name' => 'BrightSpark Electricals',  'category' => 'electrical',    'contact_person' => 'Suresh Kumar',   'phone' => '9800002222', 'status' => 'active'],
            ['name' => 'CleanPro Housekeeping',    'category' => 'housekeeping',  'contact_person' => 'Meena Sharma',   'phone' => '9800003333', 'status' => 'active'],
            ['name' => 'SafeGuard Security',       'category' => 'security',      'contact_person' => 'Arjun Singh',    'phone' => '9800004444', 'status' => 'active'],
            ['name' => 'LiftMasters Elevators',    'category' => 'elevator',      'contact_person' => 'Priya Nair',     'phone' => '9800005555', 'status' => 'inactive'],
        ];

        $createdVendors = [];
        foreach ($vendors as $data) {
            $createdVendors[] = Vendor::create([
                ...$data,
                'email'          => strtolower(Str::slug($data['name'])).'@example.com',
                'rating'         => 0,
                'ratings_count'  => 0,
            ]);
        }

        [$aquafix, $brightspark, $cleanpro, $safeguard, $liftmaster] = $createdVendors;

        // --- Contracts ---
        VendorContract::create([
            'vendor_id'       => $aquafix->id,
            'title'           => 'Annual Plumbing Maintenance',
            'contract_number' => 'CNT-2026-001',
            'start_date'      => now()->startOfYear(),
            'end_date'        => now()->endOfYear(),
            'value'           => 120000.00,
            'status'          => 'active',
            'terms'           => 'Monthly inspections + emergency callouts within 4 hours.',
        ]);

        VendorContract::create([
            'vendor_id'       => $cleanpro->id,
            'title'           => 'Common Area Cleaning Contract',
            'contract_number' => 'CNT-2026-002',
            'start_date'      => now()->subMonths(3),
            'end_date'        => now()->addMonths(9),
            'value'           => 84000.00,
            'status'          => 'active',
            'terms'           => 'Daily cleaning of lobbies, staircases and parking.',
        ]);

        // --- Work Orders ---
        $workOrders = [];

        $workOrders[] = WorkOrder::create([
            'reference'     => 'WO-'.now()->format('ym').'-'.Str::upper(Str::random(5)),
            'vendor_id'     => $aquafix->id,
            'title'         => 'Fix water leakage in pump room',
            'description'   => 'Persistent leak detected near pump #2.',
            'priority'      => 'high',
            'status'        => 'completed',
            'amount'        => 8500.00,
            'scheduled_for' => now()->subDays(10),
            'completed_at'  => now()->subDays(8),
            'created_by'    => $admin?->id,
        ]);

        $workOrders[] = WorkOrder::create([
            'reference'     => 'WO-'.now()->format('ym').'-'.Str::upper(Str::random(5)),
            'vendor_id'     => $brightspark->id,
            'title'         => 'Replace corridor lighting — Block B',
            'priority'      => 'medium',
            'status'        => 'in_progress',
            'amount'        => 12000.00,
            'scheduled_for' => now()->addDays(3),
            'created_by'    => $admin?->id,
        ]);

        $workOrders[] = WorkOrder::create([
            'reference'     => 'WO-'.now()->format('ym').'-'.Str::upper(Str::random(5)),
            'vendor_id'     => $cleanpro->id,
            'title'         => 'Deep cleaning of basement parking',
            'priority'      => 'low',
            'status'        => 'open',
            'amount'        => 5000.00,
            'scheduled_for' => now()->addDays(7),
            'created_by'    => $admin?->id,
        ]);

        $workOrders[] = WorkOrder::create([
            'reference'     => 'WO-'.now()->format('ym').'-'.Str::upper(Str::random(5)),
            'vendor_id'     => $safeguard->id,
            'title'         => 'CCTV camera installation — main gate',
            'priority'      => 'high',
            'status'        => 'assigned',
            'amount'        => 35000.00,
            'scheduled_for' => now()->addDays(5),
            'created_by'    => $admin?->id,
        ]);

        $workOrders[] = WorkOrder::create([
            'reference'     => 'WO-'.now()->format('ym').'-'.Str::upper(Str::random(5)),
            'vendor_id'     => $liftmaster->id,
            'title'         => 'Elevator annual maintenance service',
            'priority'      => 'medium',
            'status'        => 'cancelled',
            'amount'        => 18000.00,
            'scheduled_for' => now()->subDays(5),
            'created_by'    => $admin?->id,
        ]);

        $workOrders[] = WorkOrder::create([
            'reference'     => 'WO-'.now()->format('ym').'-'.Str::upper(Str::random(5)),
            'vendor_id'     => $aquafix->id,
            'title'         => 'Overhead tank cleaning',
            'priority'      => 'medium',
            'status'        => 'completed',
            'amount'        => 3500.00,
            'scheduled_for' => now()->subDays(20),
            'completed_at'  => now()->subDays(18),
            'created_by'    => $admin?->id,
        ]);

        // --- Payments ---
        VendorPayment::create([
            'vendor_id'    => $aquafix->id,
            'work_order_id'=> $workOrders[0]->id,
            'amount'       => 8500.00,
            'method'       => 'bank_transfer',
            'reference'    => 'NEFT-2026-001',
            'paid_at'      => now()->subDays(7),
            'recorded_by'  => $admin?->id,
            'notes'        => 'Full payment for pump room repair.',
        ]);

        VendorPayment::create([
            'vendor_id'    => $aquafix->id,
            'work_order_id'=> $workOrders[5]->id,
            'amount'       => 3500.00,
            'method'       => 'upi',
            'reference'    => 'UPI-20260515',
            'paid_at'      => now()->subDays(17),
            'recorded_by'  => $admin?->id,
        ]);

        VendorPayment::create([
            'vendor_id'   => $cleanpro->id,
            'amount'      => 7000.00,
            'method'      => 'cheque',
            'reference'   => 'CHQ-112233',
            'paid_at'     => now()->subDays(30),
            'recorded_by' => $admin?->id,
            'notes'       => 'Monthly cleaning advance.',
        ]);

        // --- Ratings ---
        $ratingsData = [
            [$aquafix,    5, 'Excellent work — fixed the leak quickly.'],
            [$aquafix,    4, 'Good service, slight delay but resolved well.'],
            [$cleanpro,   4, 'Common areas are noticeably cleaner.'],
            [$safeguard,  3, 'Response time could be better.'],
            [$brightspark, 5, 'Professional team, neat cabling.'],
        ];

        foreach ($ratingsData as [$vendor, $stars, $comment]) {
            VendorRating::create([
                'society_id' => $society->id,
                'vendor_id'  => $vendor->id,
                'user_id'    => $admin?->id,
                'rating'     => $stars,
                'comment'    => $comment,
            ]);
        }

        // Recompute ratings for all affected vendors
        foreach ($createdVendors as $vendor) {
            $vendor->recalcRating();
        }

        tenancy()->forget();
    }
}
