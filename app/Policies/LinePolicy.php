<?php

namespace App\Policies;

use App\Models\Line;
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
            if ($access) {
                if ($uac->hasRole('admin')) {
                    return true;
                }
                if ($uac->hasPermissionTo('view') && in_array($line->id, $access->line_id)){
                    return true;
                }
                return $this->deny("Not found", 403);
            }
            return true;
        } catch(Exception $e) {
            return $this->deny("Not found", 403);
        }
    }

    public function editLine(User $user, Line $line, $acc_id) // Work for update, delete methods
    {
        try {
            $uac = $user->getAccount($acc_id)->pivot;
            $access = json_decode($uac->user_access);
            if ($access) {
                if ($uac->hasRole('admin')) {
                    return true;
                }
                if ($uac->hasPermissionTo('edit') && in_array($line->id, $access->line_id)){
                    return true;
                }
                return $this->deny("Not found", 403);
            }
            return true;
        } catch(Exception $e) {
            return $this->deny("Not found", 403);
        }
    }
}

