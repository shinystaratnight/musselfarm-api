<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\User;
use App\Models\Account;
use Illuminate\Auth\Access\HandlesAuthorization;

class FarmPolicy
{
    use HandlesAuthorization;

    public function show(User $user, Farm $farm, $acc_id){
        try {
            $uac = $user->getAccount($acc_id)->pivot;
            $access = json_decode($uac->user_access);
            if ($access) {
                if ($uac->hasRole('admin')) {
                    return true;
                }
                if ($uac->hasPermissionTo('view') && in_array($farm->id, $access->farm_id)){
                    return true;
                }
                return false;
            }
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    public function update(User $user, Farm $farm, $acc_id)
    {
        try {
            $uac = $user->getAccount($acc_id)->pivot;
            $access = json_decode($uac->user_access);
            if ($access) {
                if ($uac->hasRole('admin')) {
                    return true;
                }
                if ($uac->hasPermissionTo('edit') && in_array($farm->id, $access->farm_id)){
                    return true;
                }
                return false;
            }
            return true;
        } catch(Exception $e) {
            return false;
        }
    }
}
