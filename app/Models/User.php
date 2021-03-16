<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, Billable, HasRoles, CascadeSoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $cascadeDeletes = ['profile'];

    protected $fillable = ['email', 'password', 'coupon', 'quantity', 'active', 'activation_token', 'account_id'];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'trial_ends_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'activation_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function profile()
    {
        return $this->hasOne(UserProfile::class)->withTrashed();
    }

    public function invite()
    {
        return $this->belongsToMany(Invite::class, 'user_id', 'id');
    }

    public function inviting()
    {
        return $this->hasMany(Inviting::class,'inviting_user_id', 'id');
    }

    public function farms()
    {
        return $this->belongsToMany(Farm::class)->withPivot('user_id');
    }

    public function lines()
    {
        return $this->belongsToMany(Line::class)->withPivot('user_id');
    }

    public function changeEmails()
    {
        return $this->hasOne(ChangeEmail::class, 'user_id', 'id');
    }

    public function getOwner()
    {
        $owner = Inviting::where('invited_user_id', auth()->user()->id)->first();

        $user = User::find($owner->inviting_user_id);

        return $user;
    }

    public function getAccount()
    {
        return Account::where('id', $this->account_id)->first();
    }
}
