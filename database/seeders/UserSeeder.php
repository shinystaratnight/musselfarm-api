<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\Line;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'email' => 'join@musselfarm.co.nz',
            'password' => Hash::make('*tR352TZ%8^+2spt'),
            'active' => true
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'name' => 'John Doe'
        ]);

        $user->assignRole('owner');
        $user->givePermissionTo('view');
        $user->givePermissionTo('edit');
        $user->givePermissionTo('finance');

        Farm::create([
            'user_id' => 1,
            'name' => 'Test farm 1',
            'long' => -139.1234560,
            'lat' => 45.1234560,
            'area' => 450,
            'owner' => '[{"title":"Jane","percent":"64"},{"title":"John","percent":"36"}]'
        ]);

//        Line::create([
//            'line_name' => 'Test line 1',
//            'farm_id' => 1,
//            'length' => 950
//        ]);

        $user->farms()->attach(1);
//        $user->lines()->attach(1);
    }
}
