<?php

namespace Database\Seeders;

use App\Models\RiskWeight;
use Illuminate\Database\Seeder;

class RiskWeightSeeder extends Seeder
{
    public function run(): void
    {
        RiskWeight::firstOrCreate(
            ['is_active' => true],
            [
                'weather_weight' => 30,
                'inflation_weight' => 20,
                'political_weight' => 40,
                'currency_weight' => 10,
            ]
        );

        $this->command->info('RiskWeightSeeder: default weights (30/20/40/10) seeded.');
    }
}
