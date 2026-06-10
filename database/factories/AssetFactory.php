<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $purchaseCost = $this->faker->randomFloat(2, 10000, 500000);
        $salvageValue = round($purchaseCost * 0.1, 2);
        $method       = $this->faker->randomElement(['straight_line', 'declining_balance', 'none']);

        return [
            'code'                 => strtoupper($this->faker->bothify('AST-###')),
            'name'                 => $this->faker->words(3, true),
            'description'          => $this->faker->sentence(),
            'location'             => $this->faker->randomElement(['Basement', 'Terrace', 'Lobby', 'Common Area', 'Pump Room']),
            'purchase_date'        => $this->faker->dateTimeBetween('-8 years', '-1 year')->format('Y-m-d'),
            'purchase_cost'        => $purchaseCost,
            'salvage_value'        => $salvageValue,
            'depreciation_method'  => $method,
            'current_value'        => $purchaseCost,
            'status'               => $this->faker->randomElement(['active', 'under_maintenance', 'active', 'active']),
        ];
    }
}
