<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\ParkingSlot;
use App\Models\Society;
use App\Models\Tower;
use App\Services\Structure\StructureService;
use Illuminate\Database\Seeder;

class StructureSeeder extends Seeder
{
    public function run(StructureService $service): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        tenancy()->set($society);

        if (Tower::where('society_id', $society->id)->exists()) {
            tenancy()->forget();

            return;
        }

        foreach ([['Tower A', 'A', 6, 4], ['Tower B', 'B', 8, 4]] as [$name, $code, $floors, $perFloor]) {
            $service->createTower([
                'society_id'      => $society->id,
                'name'            => $name,
                'code'            => $code,
                'type'            => 'tower',
                'total_floors'    => $floors,
                'units_per_floor' => $perFloor,
                'status'          => 'active',
            ], scaffold: true);
        }

        // Give every unit a realistic type + carpet area, and occupy a portion.
        $resident = $society->users()->whereHas('roles', fn ($q) => $q->where('slug', 'resident'))->first();
        $flats = \App\Models\Flat::where('society_id', $society->id)->get();
        $types = [['1BHK', 620, 1, 1], ['2BHK', 950, 2, 2], ['3BHK', 1340, 3, 2]];
        foreach ($flats as $i => $flat) {
            [$type, $area, $beds, $baths] = $types[$i % 3];
            $flat->update([
                'type'          => $type,
                'carpet_area'   => $area,
                'built_up_area' => round($area * 1.25),
                'bedrooms'      => $beds,
                'bathrooms'     => $baths,
                'status'        => $i % 4 === 0 ? 'vacant' : 'occupied',
            ]);
        }
        if ($resident && $first = $flats->first()) {
            $first->update(['owner_id' => $resident->id, 'status' => 'occupied']);
        }

        foreach (range(1, 12) as $n) {
            ParkingSlot::create([
                'society_id' => $society->id,
                'code'       => 'P-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                'type'       => $n <= 8 ? 'car' : 'bike',
                'location'   => 'Basement '.(($n % 2) + 1),
                'status'     => 'available',
            ]);
        }

        tenancy()->forget();
    }
}
