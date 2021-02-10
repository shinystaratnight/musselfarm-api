<?php

namespace Database\Seeders;

use App\Models\LineBudget;
use Illuminate\Database\Seeder;

class LineBudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LineBudget::create([
            'line_id' => 1,
            'planned_harvest_tones' => 2340.00,
            'budgeted_harvest_income' => 22300.50,
            'start_budget' => 1546300800,
            'end_budget' => 1577836799,
            'length_actual' => 789,
            'length_budget' => 950,
            'planned_harvest_tones_actual' => 3500.03,
            'budgeted_harvest_income_actual' => 26300.50,
        ]);

        LineBudget::create([
            'line_id' => 1,
            'planned_harvest_tones' => 2240.00,
            'budgeted_harvest_income' => 17300.50,
            'start_budget' => 1577836800,
            'end_budget' => 1609459199,
            'length_actual' => 887,
            'length_budget' => 950,
            'planned_harvest_tones_actual' => 2500.03,
            'budgeted_harvest_income_actual' => 19300.50,
        ]);

        LineBudget::create([
            'line_id' => 1,
            'planned_harvest_tones' => 3340.00,
            'budgeted_harvest_income' => 13300.50,
            'start_budget' => 1609459200,
            'end_budget' => 0,
            'length_actual' => 769,
            'length_budget' => 950,
            'planned_harvest_tones_actual' => 3200.03,
            'budgeted_harvest_income_actual' => 9300.50,
        ]);
    }
}
