<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenanceLog;
use App\Models\AssetMaintenanceSchedule;
use App\Models\Society;
use App\Models\Tower;
use App\Services\Assets\AssetService;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        tenancy()->set($society);

        // ------------------------------------------------------------------ //
        // Categories
        // ------------------------------------------------------------------ //
        $categoryData = [
            ['Elevators',       10.0, 15],
            ['Generators',      15.0, 10],
            ['Water Pumps',     12.0, 12],
            ['Fire Equipment',   8.0, 20],
        ];

        $categories = [];
        foreach ($categoryData as [$name, $rate, $life]) {
            $categories[$name] = AssetCategory::firstOrCreate(
                ['society_id' => $society->id, 'name' => $name],
                ['depreciation_rate' => $rate, 'useful_life_years' => $life, 'is_active' => true]
            );
        }

        if (Asset::where('society_id', $society->id)->exists()) {
            tenancy()->forget();
            return;
        }

        $tower = Tower::where('society_id', $society->id)->first();

        // ------------------------------------------------------------------ //
        // Assets
        // ------------------------------------------------------------------ //
        $assetsData = [
            [
                'name'               => 'Main Elevator A',
                'code'               => 'ELV-001',
                'category'           => 'Elevators',
                'location'           => 'Tower A – Lobby',
                'purchase_date'      => now()->subYears(5)->toDateString(),
                'purchase_cost'      => 1200000.00,
                'salvage_value'      => 120000.00,
                'depreciation_method'=> 'straight_line',
            ],
            [
                'name'               => 'Main Elevator B',
                'code'               => 'ELV-002',
                'category'           => 'Elevators',
                'location'           => 'Tower B – Lobby',
                'purchase_date'      => now()->subYears(3)->toDateString(),
                'purchase_cost'      => 1300000.00,
                'salvage_value'      => 130000.00,
                'depreciation_method'=> 'straight_line',
            ],
            [
                'name'               => 'Diesel Generator 1',
                'code'               => 'GEN-001',
                'category'           => 'Generators',
                'location'           => 'Basement – Generator Room',
                'purchase_date'      => now()->subYears(4)->toDateString(),
                'purchase_cost'      => 850000.00,
                'salvage_value'      => 85000.00,
                'depreciation_method'=> 'declining_balance',
            ],
            [
                'name'               => 'Backup Generator',
                'code'               => 'GEN-002',
                'category'           => 'Generators',
                'location'           => 'Basement – Generator Room',
                'purchase_date'      => now()->subYears(2)->toDateString(),
                'purchase_cost'      => 920000.00,
                'salvage_value'      => 92000.00,
                'depreciation_method'=> 'declining_balance',
            ],
            [
                'name'               => 'Underground Water Pump',
                'code'               => 'PMP-001',
                'category'           => 'Water Pumps',
                'location'           => 'Basement – Pump Room',
                'purchase_date'      => now()->subYears(6)->toDateString(),
                'purchase_cost'      => 250000.00,
                'salvage_value'      => 25000.00,
                'depreciation_method'=> 'straight_line',
            ],
            [
                'name'               => 'Overhead Pump Motor',
                'code'               => 'PMP-002',
                'category'           => 'Water Pumps',
                'location'           => 'Terrace – Pump House',
                'purchase_date'      => now()->subYears(1)->toDateString(),
                'purchase_cost'      => 180000.00,
                'salvage_value'      => 18000.00,
                'depreciation_method'=> 'straight_line',
            ],
            [
                'name'               => 'Fire Extinguisher Set (Floor 1–5)',
                'code'               => 'FIRE-001',
                'category'           => 'Fire Equipment',
                'location'           => 'Floors 1–5',
                'purchase_date'      => now()->subYears(2)->toDateString(),
                'purchase_cost'      => 45000.00,
                'salvage_value'      => 0.00,
                'depreciation_method'=> 'straight_line',
            ],
            [
                'name'               => 'Hydrant System',
                'code'               => 'FIRE-002',
                'category'           => 'Fire Equipment',
                'location'           => 'Stairwell – All Floors',
                'purchase_date'      => now()->subYears(3)->toDateString(),
                'purchase_cost'      => 320000.00,
                'salvage_value'      => 32000.00,
                'depreciation_method'=> 'straight_line',
            ],
        ];

        $service = app(AssetService::class);

        foreach ($assetsData as $row) {
            $category = $categories[$row['category']];
            unset($row['category']);

            $asset = $service->create(array_merge($row, [
                'tower_id'          => $tower?->id,
                'status'            => 'active',
                'useful_life_years' => $category->useful_life_years,
            ]));

            // Attach category after creation (category_id passed to create).
            $asset->update(['asset_category_id' => $category->id]);
            $service->recomputeDepreciation($asset->refresh());
        }

        // ------------------------------------------------------------------ //
        // Maintenance Schedules
        // ------------------------------------------------------------------ //
        $assets = Asset::where('society_id', $society->id)->get()->keyBy('code');

        $scheduleData = [
            'ELV-001' => [
                ['title' => 'Monthly Elevator Inspection',     'frequency' => 'monthly',   'next_due_date' => now()->subDays(5)->toDateString(),  'status' => 'overdue', 'estimated_cost' => 5000],
                ['title' => 'Annual Elevator Servicing',       'frequency' => 'yearly',    'next_due_date' => now()->addMonths(2)->toDateString(), 'status' => 'scheduled', 'estimated_cost' => 25000],
            ],
            'GEN-001' => [
                ['title' => 'Monthly Generator Maintenance',   'frequency' => 'monthly',   'next_due_date' => now()->addDays(3)->toDateString(),   'status' => 'due', 'estimated_cost' => 3500],
            ],
            'PMP-001' => [
                ['title' => 'Quarterly Pump Servicing',        'frequency' => 'quarterly', 'next_due_date' => now()->addMonth()->toDateString(),   'status' => 'scheduled', 'estimated_cost' => 8000],
            ],
            'FIRE-001' => [
                ['title' => 'Annual Fire Extinguisher Refill', 'frequency' => 'yearly',    'next_due_date' => now()->subDays(15)->toDateString(),  'status' => 'overdue', 'estimated_cost' => 12000],
            ],
        ];

        foreach ($scheduleData as $code => $schedules) {
            $asset = $assets->get($code);
            if (! $asset) {
                continue;
            }

            foreach ($schedules as $s) {
                AssetMaintenanceSchedule::create(array_merge($s, [
                    'society_id' => $society->id,
                    'asset_id'   => $asset->id,
                ]));
            }
        }

        // ------------------------------------------------------------------ //
        // Maintenance Logs
        // ------------------------------------------------------------------ //
        $elvAsset = $assets->get('ELV-001');
        if ($elvAsset) {
            AssetMaintenanceLog::create([
                'society_id'   => $society->id,
                'asset_id'     => $elvAsset->id,
                'performed_on' => now()->subMonths(2)->toDateString(),
                'cost'         => 4800.00,
                'performed_by' => 'Otis Service Team',
                'notes'        => 'Routine monthly inspection completed. Cables and brakes checked.',
            ]);
        }

        $genAsset = $assets->get('GEN-001');
        if ($genAsset) {
            AssetMaintenanceLog::create([
                'society_id'   => $society->id,
                'asset_id'     => $genAsset->id,
                'performed_on' => now()->subMonths(1)->toDateString(),
                'cost'         => 3200.00,
                'performed_by' => 'Cummins Engineer',
                'notes'        => 'Oil change, fuel filter replaced, load test done.',
            ]);
        }

        tenancy()->forget();
    }
}
