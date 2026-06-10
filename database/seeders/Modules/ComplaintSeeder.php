<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\ComplaintCategory;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComplaintSeeder extends Seeder
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
        $staff = User::withoutGlobalScopes()->where('email', 'maintenance@greenvalley.test')->first();

        $categories = collect([
            ['Plumbing', 24], ['Electrical', 24], ['Housekeeping', 48], ['Security', 12], ['Elevator', 8],
        ])->map(fn ($c) => ComplaintCategory::firstOrCreate(
            ['society_id' => $society->id, 'name' => $c[0]],
            ['slug' => Str::slug($c[0]), 'sla_hours' => $c[1]]
        ));

        if (Complaint::where('society_id', $society->id)->exists()) {
            tenancy()->forget();
            return;
        }

        foreach (range(1, 12) as $i) {
            $category = $categories->random();
            $status = ['open', 'assigned', 'in_progress', 'resolved', 'closed'][array_rand(['open', 'assigned', 'in_progress', 'resolved', 'closed'])];

            $complaint = Complaint::create([
                'reference'             => 'CMP-'.now()->format('ym').'-'.Str::upper(Str::random(5)),
                'complaint_category_id' => $category->id,
                'raised_by'             => $resident?->id,
                'assigned_to'           => in_array($status, ['open']) ? null : $staff?->id,
                'title'                 => "Sample {$category->name} issue #{$i}",
                'description'           => 'Auto-generated demo complaint for testing dashboards and reports.',
                'priority'              => ['low', 'medium', 'high', 'critical'][array_rand([0, 1, 2, 3])],
                'status'                => $status,
                'sla_due_at'            => now()->addHours($category->sla_hours),
                'resolved_at'           => in_array($status, ['resolved', 'closed']) ? now()->subDays(rand(0, 5)) : null,
                'created_at'            => now()->subDays(rand(0, 25)),
            ]);

            ComplaintActivity::create([
                'society_id'   => $society->id,
                'complaint_id' => $complaint->id,
                'user_id'      => $resident?->id,
                'action'       => 'created',
                'note'         => 'Complaint registered (seed)',
            ]);
        }

        tenancy()->forget();
    }
}
