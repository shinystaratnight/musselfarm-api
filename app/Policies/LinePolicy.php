<?php

namespace App\Policies;

use App\Models\Line;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LinePolicy
{
    use HandlesAuthorization;

    public function viewLine(User $user, Line $line) // Work for index, show methods
    {
        if(in_array($user->id, $line->users->pluck('id')->toArray()) && $user->can('view')) {
            return true;
        } else {
            return $this->deny("Not found", 403);
        }
    }

    public function editLine(User $user, Line $line) // Work for update, delete methods
    {
        if(in_array($user->id, $line->users->pluck('id')->toArray()) && $user->can('edit')) {
            return true;
        } else {
            return $this->deny("Not found", 403);
        }
    }
}

