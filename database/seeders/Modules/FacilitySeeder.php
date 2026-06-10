<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\Flat;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        tenancy()->set($society);

        $resident = User::withoutGlobalScopes()->where('email', 'resident@greenvalley.test')->first();
        $flat     = Flat::first();

        // Guard re-seed
        if (Facility::where('society_id', $society->id)->exists()) {
            tenancy()->forget();
            return;
        }

        $facilities = [
            [
                'name'              => 'Clubhouse',
                'type'              => 'clubhouse',
                'description'       => 'A spacious clubhouse for community events and celebrations.',
                'capacity'          => 150,
                'charge'            => 2000.00,
                'requires_approval' => true,
                'opening_time'      => '08:00',
                'closing_time'      => '22:00',
                'slot_minutes'      => 120,
                'is_active'         => true,
            ],
            [
                'name'              => 'Gym',
                'type'              => 'gym',
                'description'       => 'Fully equipped fitness center open for all residents.',
                'capacity'          => 20,
                'charge'            => 0.00,
                'requires_approval' => false,
                'opening_time'      => '05:30',
                'closing_time'      => '22:00',
                'slot_minutes'      => 60,
                'is_active'         => true,
            ],
            [
                'name'              => 'Swimming Pool',
                'type'              => 'pool',
                'description'       => 'Olympic-size swimming pool with changing rooms.',
                'capacity'          => 30,
                'charge'            => 100.00,
                'requires_approval' => false,
                'opening_time'      => '06:00',
                'closing_time'      => '20:00',
                'slot_minutes'      => 60,
                'is_active'         => true,
            ],
            [
                'name'              => 'Community Hall',
                'type'              => 'hall',
                'description'       => 'Large hall for meetings, functions and cultural events.',
                'capacity'          => 200,
                'charge'            => 5000.00,
                'requires_approval' => true,
                'opening_time'      => '09:00',
                'closing_time'      => '22:00',
                'slot_minutes'      => 180,
                'is_active'         => true,
            ],
            [
                'name'              => 'Tennis Court',
                'type'              => 'court',
                'description'       => 'Floodlit tennis court available for individual and group play.',
                'capacity'          => 4,
                'charge'            => 200.00,
                'requires_approval' => false,
                'opening_time'      => '06:00',
                'closing_time'      => '21:00',
                'slot_minutes'      => 60,
                'is_active'         => true,
            ],
        ];

        $createdFacilities = collect($facilities)->map(fn (array $f) => Facility::create($f));

        // Seed ~6 bookings with varied statuses across facilities
        $bookingSeed = [
            [
                'facility' => 'Gym',
                'booking_date' => now()->subDays(3)->format('Y-m-d'),
                'start_time'   => '07:00',
                'end_time'     => '08:00',
                'status'       => 'completed',
                'guests'       => 0,
            ],
            [
                'facility' => 'Swimming Pool',
                'booking_date' => now()->subDays(1)->format('Y-m-d'),
                'start_time'   => '08:00',
                'end_time'     => '09:00',
                'status'       => 'approved',
                'guests'       => 2,
            ],
            [
                'facility' => 'Tennis Court',
                'booking_date' => now()->format('Y-m-d'),
                'start_time'   => '07:00',
                'end_time'     => '08:00',
                'status'       => 'approved',
                'guests'       => 1,
            ],
            [
                'facility' => 'Clubhouse',
                'booking_date' => now()->addDays(2)->format('Y-m-d'),
                'start_time'   => '10:00',
                'end_time'     => '12:00',
                'status'       => 'pending',
                'guests'       => 50,
            ],
            [
                'facility' => 'Community Hall',
                'booking_date' => now()->addDays(5)->format('Y-m-d'),
                'start_time'   => '14:00',
                'end_time'     => '17:00',
                'status'       => 'pending',
                'guests'       => 100,
            ],
            [
                'facility' => 'Swimming Pool',
                'booking_date' => now()->subDays(5)->format('Y-m-d'),
                'start_time'   => '09:00',
                'end_time'     => '10:00',
                'status'       => 'cancelled',
                'guests'       => 0,
            ],
        ];

        foreach ($bookingSeed as $seed) {
            $facility = $createdFacilities->firstWhere('name', $seed['facility']);
            if (! $facility) {
                continue;
            }

            FacilityBooking::create([
                'facility_id'  => $facility->id,
                'user_id'      => $resident?->id,
                'flat_id'      => $flat?->id,
                'booking_date' => $seed['booking_date'],
                'start_time'   => $seed['start_time'],
                'end_time'     => $seed['end_time'],
                'guests'       => $seed['guests'],
                'amount'       => $facility->charge,
                'status'       => $seed['status'],
                'notes'        => 'Demo booking (seeded)',
            ]);
        }

        tenancy()->forget();
    }
}
