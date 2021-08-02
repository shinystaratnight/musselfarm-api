<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Support\CascadeSoftDeletes;

class Farm extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['lines'];

    protected $fillable = ['user_id', 'name', 'long', 'lat', 'area', 'owner', 'farm_number', 'account_id'];

    // public function users()
    // {
    //     return $this->belongsToMany(User::class)->withPivot('user_id');
    // }

    public function accounts()
    {
        return $this->belongsToMany(Account::class);
    }

    public function lines()
    {
        return $this->hasMany(Line::class);
    }

    public function farm_budgets()
    {
        return $this->hasMany(FarmExpenses::class);
    }

    public function lines_budgets()
    {
        $l = request();
        $acc = $l->input('account_id');
        $uId = auth()->user()->id;

        // file_put_contents("1.txt", Account::where('id', 14)->first()->users);
        file_put_contents("1.txt", User::where('id', 10)->first()->accounts);
        // $uaObj = User::with('accounts')->whereHas('accounts', function($q) use($acc) {
        //     $q->where('accounts.id', '=', $acc);
        // })->find(auth()->user()->id)->accounts[0];

        // Account::with('users')->whereHas('users', function($q) use($uId) {
        //     $q->where('users.id', $uId);
        // })->find();

        // $hasAdminRole = AccountUser::with('roles')->whereHas('roles', function($q) {
        //     $q->where('roles.name', 'user');
        // })->where('user_id', auth()->user()->id)->where('account_id', $acc)->get() ? true : false;

        if ($uAccess == '' || $hasAdminRole) {
            return $this->hasMany(Line::class)->where('id', $l->input('line_id'))->with('budgets');
        } else if (in_array($l->input('line_id'), $uAccess->line_id)) {
            return $this->hasMany(Line::class)->where('id', $l->input('line_id'))->with('budgets');
        }
        return $this->hasMany(Line::class)->where('id', -1)->with('budgets');
    }

    public function overview_budgets()
    {
        $u = auth()->user()->id;

        return $this->hasMany(Line::class)->with('budgets');
    }
}
