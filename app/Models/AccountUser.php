<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Permission\Traits\HasRoles;

class AccountUser extends Pivot
{
    use HasFactory, HasRoles;

    protected $guard_name = 'web';

    protected $fillable = [
                            'user_id',
                            'account_id',
                            'user_access'];

    public function hasRole($user_id, $acc_id, $role) {
        $r = AccountUser::with('roles')->whereHas('roles', function($q) use($role) {
            $q->where('roles.name', $role);
        })->where('user_id', auth()->user()->id)->where('account_id', $acc)->get();
        if ($r) return true;
        return false;
    }
}
