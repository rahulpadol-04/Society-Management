<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MaintenanceBill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MaintenanceBillFactory extends Factory
{
    protected $model = MaintenanceBill::class;

    public function definition(): array
    {
        $subtotal   = $this->faker->randomFloat(2, 500, 5000);
        $taxAmount  = round($subtotal * 0.18, 2);
        $total      = round($subtotal + $taxAmount, 2);
        $status     = $this->faker->randomElement(['unpaid', 'partial', 'paid', 'overdue']);
        $paidAmount = match ($status) {
            'paid'    => $total,
            'partial' => round($total * $this->faker->randomFloat(2, 0.1, 0.9), 2),
            default   => 0.0,
        };
        $period = now()->format('Y-m');

        return [
            'bill_number' => 'INV-'.now()->format('ym').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'period'      => $period,
            'bill_date'   => now()->toDateString(),
            'due_date'    => now()->addDays(15)->toDateString(),
            'subtotal'    => $subtotal,
            'tax_amount'  => $taxAmount,
            'late_fee'    => 0.0,
            'discount'    => 0.0,
            'total'       => $total,
            'paid_amount' => $paidAmount,
            'status'      => $status,
            'line_items'  => [
                ['head' => 'Maintenance Charge', 'amount' => $subtotal, 'tax' => $taxAmount, 'type' => 'fixed'],
            ],
        ];
    }

    public function unpaid(): static
    {
        return $this->state(['status' => 'unpaid', 'paid_amount' => 0.0]);
    }

    public function paid(): static
    {
        return $this->state(function (array $attrs) {
            return ['status' => 'paid', 'paid_amount' => $attrs['total']];
        });
    }

    public function overdue(): static
    {
        return $this->state([
            'status'   => 'overdue',
            'due_date' => now()->subDays(5)->toDateString(),
        ]);
    }
}
