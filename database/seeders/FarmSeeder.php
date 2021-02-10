<?php

namespace Database\Seeders;

use App\Models\Farm;
use Illuminate\Database\Seeder;

class FarmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Farm::create([
            'user_id' => 1,
            'name' => 'Test farm 1',
            'long' => -139.1234560,
            'lat' => 45.1234560,
            'area' => 450,
            'owner' => '[{"title":"Jane","percent":"64"},{"title":"John","percent":"36"}]'
        ]);
    }
}
