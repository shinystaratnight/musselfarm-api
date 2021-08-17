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

            $farm = Farm::find($line->farm_id);

            if (!$farm) {
                return $this->deny("Not found", 403);
            }

            if ($access) {
                if ($uac->hasRole('admin') && $acc_id == $farm->account_id) {
                    return true;
                }
                if ($uac->hasPermissionTo('view') && in_array($farm->id, $access->farm_id)){
                    return true;
                }
                return false;
            }
            if ($acc_id == $farm->account_id)
                return true;
            return $this->deny("Not found", 403);
        } catch(Exception $e) {
            return false;
        }
    }

    public function update(User $user, Farm $farm, $acc_id)
    {
        try {
            $uac = $user->getAccount($acc_id)->pivot;
            $access = json_decode($uac->user_access);

            $farm = Farm::find($line->farm_id);

            if (!$farm) {
                return $this->deny("Not found", 403);
            }

            if ($access) {
                if ($uac->hasRole('admin') && $acc_id == $farm->account_id) {
                    return true;
                }
                if ($uac->hasPermissionTo('edit') && in_array($farm->id, $access->farm_id)){
                    return true;
                }
                return false;
            }
            if ($acc_id == $farm->account_id)
                return true;
            return $this->deny("Not found", 403);
        } catch(Exception $e) {
            return false;
        }
    }
}
