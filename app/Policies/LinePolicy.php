<?php

namespace App\Policies;

use App\Models\Line;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LinePolicy
{
    use HandlesAuthorization;

    public function viewLine(User $user, Line $line, $acc_id) // Work for index, show methods
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
                if ($uac->hasPermissionTo('view') && in_array($line->id, $access->line_id)){
                    return true;
                }
                return $this->deny("Not found", 403);
            }
            if ($acc_id == $farm->account_id)
                return true;
            return $this->deny("Not found", 403);
        } catch(Exception $e) {
            return $this->deny("Not found", 403);
        }
    }

    public function editLine(User $user, Line $line, $acc_id) // Work for update, delete methods
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
                if ($uac->hasPermissionTo('edit') && in_array($line->id, $access->line_id)){
                    return true;
                }
                return $this->deny("Not found", 403);
            }
            if ($acc_id == $farm->account_id)
                return true;
            return $this->deny("Not found", 403);
        } catch(Exception $e) {
            return $this->deny("Not found", 403);
        }
    }
}

