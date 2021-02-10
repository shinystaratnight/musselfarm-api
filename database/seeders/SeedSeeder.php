<?php

namespace Database\Seeders;

use App\Models\Seed;
use Illuminate\Database\Seeder;

class SeedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Seed::create([
            'name' => 'K'
        ]);

        Seed::create([
            'name' => 'D'
        ]);
    }
}
