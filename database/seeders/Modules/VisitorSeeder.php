<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\Flat;
use App\Models\Society;
use App\Models\User;
use App\Models\VisitorLog;
use App\Models\VisitorPass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VisitorSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        // Resolve tenant so BelongsToTenant stamps society_id automatically.
        tenancy()->set($society);

        $resident = User::withoutGlobalScopes()->where('email', 'resident@greenvalley.test')->first();
        $guard    = User::withoutGlobalScopes()
            ->where('society_id', $society->id)
            ->whereHas('roles', fn ($q) => $q->where('slug', 'security-guard'))
            ->first();

        $flat = Flat::where('society_id', $society->id)->first();

        if (VisitorPass::where('society_id', $society->id)->exists()) {
            tenancy()->forget();

            return;
        }

        // ── Seed a handful of visitor passes ────────────────────────────────

        $passDefs = [
            ['name' => 'Amit Shah',    'type' => 'guest',    'status' => 'approved'],
            ['name' => 'Priya Mehta',  'type' => 'delivery', 'status' => 'approved'],
            ['name' => 'Rohit Kumar',  'type' => 'cab',      'status' => 'approved'],
            ['name' => 'Sunita Verma', 'type' => 'service',  'status' => 'pending'],
            ['name' => 'Anil Sharma',  'type' => 'vendor',   'status' => 'approved'],
        ];

        $passes = [];

        foreach ($passDefs as $def) {
            $isApproved = $def['status'] === 'approved';

            $pass = VisitorPass::create([
                'code'           => 'VP-'.now()->format('ym').'-'.Str::upper(Str::random(5)),
                'flat_id'        => $flat?->id,
                'host_id'        => $resident?->id,
                'name'           => $def['name'],
                'phone'          => '98'.rand(10000000, 99999999),
                'type'           => $def['type'],
                'purpose'        => 'Demo visit - seeded',
                'expected_at'    => now()->addDays(rand(0, 3)),
                'valid_until'    => now()->addDays(7),
                'max_entries'    => 3,
                'entries_used'   => 0,
                'status'         => $def['status'],
                'approved_by'    => $isApproved ? $resident?->id : null,
                'approved_at'    => $isApproved ? now() : null,
            ]);

            $passes[] = $pass;
        }

        // ── Seed visitor logs spread across the last 14 days ────────────────
        // This ensures the dashboard visitor-trend chart has data across days.

        $logDefs = [
            ['name' => 'Visitor A',  'type' => 'guest',    'daysAgo' => 0,  'status' => 'in'],
            ['name' => 'Visitor B',  'type' => 'delivery', 'daysAgo' => 0,  'status' => 'out'],
            ['name' => 'Visitor C',  'type' => 'cab',      'daysAgo' => 1,  'status' => 'out'],
            ['name' => 'Visitor D',  'type' => 'guest',    'daysAgo' => 1,  'status' => 'out'],
            ['name' => 'Visitor E',  'type' => 'service',  'daysAgo' => 2,  'status' => 'out'],
            ['name' => 'Visitor F',  'type' => 'guest',    'daysAgo' => 3,  'status' => 'out'],
            ['name' => 'Visitor G',  'type' => 'vendor',   'daysAgo' => 4,  'status' => 'out'],
            ['name' => 'Visitor H',  'type' => 'guest',    'daysAgo' => 5,  'status' => 'out'],
            ['name' => 'Visitor I',  'type' => 'delivery', 'daysAgo' => 7,  'status' => 'out'],
            ['name' => 'Visitor J',  'type' => 'guest',    'daysAgo' => 9,  'status' => 'out'],
            ['name' => 'Visitor K',  'type' => 'cab',      'daysAgo' => 11, 'status' => 'out'],
            ['name' => 'Visitor L',  'type' => 'guest',    'daysAgo' => 13, 'status' => 'out'],
        ];

        foreach ($logDefs as $i => $def) {
            $checkedIn  = now()->subDays($def['daysAgo'])->setHour(rand(9, 20))->setMinute(rand(0, 59));
            $checkedOut = $def['status'] === 'out' ? $checkedIn->copy()->addMinutes(rand(15, 120)) : null;
            $pass       = $passes[$i % count($passes)] ?? null;

            VisitorLog::create([
                'visitor_pass_id' => ($pass && $pass->status === 'approved') ? $pass->id : null,
                'flat_id'         => $flat?->id,
                'guard_id'        => $guard?->id,
                'name'            => $def['name'],
                'phone'           => '98'.rand(10000000, 99999999),
                'type'            => $def['type'],
                'purpose'         => 'Demo visit - seeded',
                'gate'            => ['Main Gate', 'Side Gate'][rand(0, 1)],
                'checked_in_at'   => $checkedIn,
                'checked_out_at'  => $checkedOut,
                'status'          => $def['status'],
                'created_at'      => $checkedIn,
                'updated_at'      => $checkedOut ?? $checkedIn,
            ]);
        }

        tenancy()->forget();
    }
}
