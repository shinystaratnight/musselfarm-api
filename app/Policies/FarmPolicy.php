<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FarmPolicy
{
    use HandlesAuthorization;

    public function show(User $user, Farm $farm){
        if(in_array($user->id, $farm->users->pluck('id')->toArray()) && $user->can('view')) {
            return true;
        } else {
            return false;
        }
    }

    public function update(User $user, Farm $farm)
    {
        if(in_array($user->id, $farm->users->pluck('id')->toArray()) && $user->can('edit')) {
            return true;
        } else {
            return false;
        }
    }
}
