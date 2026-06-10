<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\EmergencyContact;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Society;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class ResidentSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        // Resolve tenant so BelongsToTenant stamps society_id automatically.
        tenancy()->set($society);

        // Guard against re-seed.
        if (Resident::where('society_id', $society->id)->exists()) {
            tenancy()->forget();
            return;
        }

        // Resolve demo users by their canonical email addresses.
        $residentUser = User::withoutGlobalScopes()->where('email', 'resident@greenvalley.test')->first();

        // First occupied flat in this society.
        $primaryFlat = Flat::where('society_id', $society->id)
            ->where('status', 'occupied')
            ->orderBy('id')
            ->first();

        // ---- Primary resident linked to the demo user -----------------------
        if ($residentUser && $primaryFlat) {
            $primaryResident = Resident::create([
                'user_id'      => $residentUser->id,
                'flat_id'      => $primaryFlat->id,
                'type'         => 'owner',
                'name'         => $residentUser->name,
                'email'        => $residentUser->email,
                'phone'        => '9000100001',
                'is_primary'   => true,
                'move_in_date' => now()->subYears(2)->toDateString(),
                'status'       => 'active',
            ]);

            // Two family members.
            Resident::create([
                'flat_id'    => $primaryFlat->id,
                'parent_id'  => $primaryResident->id,
                'type'       => 'family_member',
                'name'       => 'Priya Sharma',
                'phone'      => '9000100002',
                'relation'   => 'spouse',
                'status'     => 'active',
            ]);

            Resident::create([
                'flat_id'    => $primaryFlat->id,
                'parent_id'  => $primaryResident->id,
                'type'       => 'family_member',
                'name'       => 'Aryan Sharma',
                'phone'      => '9000100003',
                'relation'   => 'child',
                'status'     => 'active',
            ]);

            // Emergency contacts.
            EmergencyContact::create([
                'society_id'  => $society->id,
                'resident_id' => $primaryResident->id,
                'name'        => 'Suresh Sharma',
                'phone'       => '9000200001',
                'relation'    => 'father',
                'is_primary'  => true,
            ]);

            EmergencyContact::create([
                'society_id'  => $society->id,
                'resident_id' => $primaryResident->id,
                'name'        => 'Meena Sharma',
                'phone'       => '9000200002',
                'relation'    => 'mother',
                'is_primary'  => false,
            ]);

            // Two vehicles: one car, one bike.
            Vehicle::create([
                'flat_id'             => $primaryFlat->id,
                'resident_id'         => $primaryResident->id,
                'type'                => 'car',
                'make'                => 'Maruti',
                'model'               => 'Swift',
                'registration_number' => 'MH01AB1234',
                'color'               => 'White',
                'status'              => 'active',
            ]);

            Vehicle::create([
                'flat_id'             => $primaryFlat->id,
                'resident_id'         => $primaryResident->id,
                'type'                => 'bike',
                'make'                => 'Honda',
                'model'               => 'Activa',
                'registration_number' => 'MH01CD5678',
                'color'               => 'Blue',
                'status'              => 'active',
            ]);
        }

        // ---- Additional standalone residents on other flats -----------------
        $otherFlats = Flat::where('society_id', $society->id)
            ->where('status', 'occupied')
            ->when($primaryFlat, fn ($q) => $q->where('id', '!=', $primaryFlat->id))
            ->orderBy('id')
            ->take(4)
            ->get();

        $extraNames = ['Rohit Verma', 'Sunita Patel', 'Kiran Mehta', 'Anil Kapoor'];

        foreach ($otherFlats as $i => $flat) {
            Resident::create([
                'flat_id'      => $flat->id,
                'type'         => 'owner',
                'name'         => $extraNames[$i] ?? 'Resident '.$i,
                'phone'        => '900030000'.($i + 1),
                'is_primary'   => true,
                'move_in_date' => now()->subMonths(rand(6, 24))->toDateString(),
                'status'       => 'active',
            ]);
        }

        tenancy()->forget();
    }
}
