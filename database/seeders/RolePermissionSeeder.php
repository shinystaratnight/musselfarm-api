<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionView = Permission::create(['name' => 'view']);
        $permissionEdit = Permission::create(['name' => 'edit']);
        $permissionFinance = Permission::create(['name' => 'finance']);

        $roleOwner = Role::create(['name' => 'owner']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        $roleOwner->givePermissionTo($permissionView);
        $roleOwner->givePermissionTo($permissionEdit);
        $roleOwner->givePermissionTo($permissionFinance);
    }
}
