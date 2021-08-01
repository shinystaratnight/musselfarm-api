<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\Models\Account;
use App\Models\AccountUser;
class UpdateUserAccountRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = User::all();
        foreach ($users as $user) {
            $role = $user->roles->first()->name;
            $user->accounts->first()->pivot->assignRole($role);
            $user->removeRole($role);
            $permissions = $user->getPermissionNames();
            foreach ( $permissions as $permission) {
                $user->accounts->first()->pivot->givePermissionTo($permission);
                $user->revokePermissionTo($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $users = User::all();
        foreach ($users as $user) {
            $role = $user->accounts->first()->pivot->roles->first()->name;
            $user->assignRole($role);
            $user->accounts->first()->pivot->removeRole($role);
            $permissions = $user->accounts->first()->pivot->getPermissionNames();
            foreach ( $permissions as $permission) {
                $user->givePermissionTo($permission);
                $user->accounts->first()->pivot->revokePermissionTo($permission);
            }
        }
    }
}
