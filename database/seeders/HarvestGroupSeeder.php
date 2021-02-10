<?php

namespace Database\Seeders;

use App\Models\HarvestGroup;
use Illuminate\Database\Seeder;

class HarvestGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        HarvestGroup::create([
            'line_id' => 1,
            'seed_id' => 2,
        ]);
    }
}
