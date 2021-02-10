<?php

namespace Database\Seeders;

use App\Models\Expenses;
use Illuminate\Database\Seeder;

class ExpensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Expenses::create([
            'line_budget_id' => 1,
            'type' => 'm',
            'expenses_name' => 'Item 1',
            'price_budget' => 230.05,
            'price_actual' => 0.00
        ]);

        Expenses::create([
            'line_budget_id' => 1,
            'type' => 's',
            'expenses_name' => 'Item 2',
            'price_budget' => 0.00,
            'price_actual' => 1030.07
        ]);

        Expenses::create([
            'line_budget_id' => 2,
            'type' => 's',
            'expenses_name' => 'Item 3',
            'price_budget' => 230.05,
            'price_actual' => 50.00
        ]);

        Expenses::create([
            'line_budget_id' => 2,
            'type' => 'm',
            'expenses_name' => 'Item 4',
            'price_budget' => 1230.05,
            'price_actual' => 2000.78
        ]);

        Expenses::create([
            'line_budget_id' => 2,
            'type' => 's',
            'expenses_name' => 'Item 5',
            'price_budget' => 230.05,
            'price_actual' => 540.50
        ]);

        Expenses::create([
            'line_budget_id' => 3,
            'type' => 'm',
            'expenses_name' => 'Item 1',
            'price_budget' => 630.05,
            'price_actual' => 631.00
        ]);

        Expenses::create([
            'line_budget_id' => 3,
            'type' => 'm',
            'expenses_name' => 'Item 2',
            'price_budget' => 0.00,
            'price_actual' => 30.07
        ]);
    }
}
